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
		'upgrade_active' => false,
		'installed' => '',
		'installed_examples' => false,
		'booktable_page' => 0,
		'compatibility_mode' => true,
		'style_pack' => 'Default',
		'image_size' => 'medium',
		'reviews_box' => 'none',
		'enable_socialmedia_badges_single_book' => false,
		'enable_socialmedia_badges_book_excerpt' => false,
		'enable_socialmedia_bar_single_book' => false,
		'enable_seo' => true,
		'enable_buybutton_shadowbox' => false,
		'enable_breadcrumbs' => true,
		'show_series' => true,
		'show_find_bookstore' => true,
		'book_button_size' => 'medium',
		'listing_button_size' => 'medium',
		'widget_button_size' => 'medium',
		'posts_per_page' => 12,
		'enable_default_affiliates' => false,
		'product_name' => __('Books', 'mybooktable'),
		'product_slug' => _x('books', 'URL slug', 'mybooktable'),
		'hide_domc_notice' => false,
		'domc_notice_text' => __('Disclosure of Material Connection: Some of the links in the page above are "affiliate links." This means if you click on the link and purchase the item, I will receive an affiliate commission. I am disclosing this in accordance with the Federal Trade Commission\'s <a href="http://www.access.gpo.gov/nara/cfr/waisidx_03/16cfr255_03.html" target="_blank">16 CFR, Part 255</a>: "Guides Concerning the Use of Endorsements and Testimonials in Advertising."', 'mybooktable'),
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

function mbt_save_author_priority($author_id, $priority) {
	$author_priorities = mbt_get_setting("author_priorities");
	if(empty($author_priorities)) { $author_priorities = array(); }
	$author_priorities[$author_id] = $priority;
	mbt_update_setting("author_priorities", $author_priorities);
}

function mbt_get_author_priority($author_id) {
	$author_priorities = mbt_get_setting("author_priorities");
	if(empty($author_priorities)) { $author_priorities = array(); }
	return isset($author_priorities[$author_id]) ? $author_priorities[$author_id] : 50;
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

function mbt_get_reviews_boxes() {
	return apply_filters('mbt_reviews_boxes', array());
}

function mbt_add_disabled_reviews_boxes($reviews) {
	$reviews['amazon'] = array(
		'name' => __('Amazon Reviews'),
		'disabled' => mbt_get_upgrade_message(),
	);
	return $reviews;
}
add_filter('mbt_reviews_boxes', 'mbt_add_disabled_reviews_boxes', 9);

function mbt_get_wp_filesystem($nonce_url) {
	ob_start();
	$creds = request_filesystem_credentials($nonce_url, '', false, false, null);
	$output = ob_get_contents();
	ob_end_clean();
	if($creds === false) { return $output; }

	if(!WP_Filesystem($creds)) {
		ob_start();
		request_filesystem_credentials($nonce_url, '', true, false, null);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	return '';
}

function mbt_download_and_insert_attachment($url) {
	$raw_response = wp_remote_get($url, array('timeout' => 3));
	if(is_wp_error($raw_response) or wp_remote_retrieve_response_code($raw_response) != 200) { return 0; }
	$file_data = wp_remote_retrieve_body($raw_response);

	$nonce_url = wp_nonce_url('admin.php', 'mbt_download_and_insert_attachment');
	$output = mbt_get_wp_filesystem($nonce_url);
	if(!empty($output)) { return 0;	}
	global $wp_filesystem;

	$url_parts = parse_url($url);
	$filename = basename($url_parts['path']);
	$filename = preg_replace('/[^A-Za-z0-9_.]/', '', $filename);
	$upload_dir = wp_upload_dir();
	$filepath = $upload_dir['path'].'/'.$filename;

	if(!$wp_filesystem->put_contents($filepath, $file_data, FS_CHMOD_FILE)) { return 0; }

	$filetype = wp_check_filetype(basename($filepath), null);
	$attachment = array(
		'guid'           => $upload_dir['url'].'/'.$filename,
		'post_mime_type' => $filetype['type'],
		'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
		'post_content'   => '',
		'post_status'    => 'inherit'
	);

	$attach_id = wp_insert_attachment($attachment, $filepath);

	require_once(ABSPATH.'wp-admin/includes/image.php');
	$attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
	wp_update_attachment_metadata($attach_id, $attach_data);

	return $attach_id;
}

function mbt_copy_and_insert_attachment($path) {
	$nonce_url = wp_nonce_url('admin.php', 'mbt_copy_and_insert_attachment');
	$output = mbt_get_wp_filesystem($nonce_url);
	if(!empty($output)) { return 0;	}
	global $wp_filesystem;

	$filename = basename($path);
	$filename = preg_replace('/[^A-Za-z0-9_.]/', '', $filename);
	$upload_dir = wp_upload_dir();
	$filepath = $upload_dir['path'].'/'.$filename;

	if(!$wp_filesystem->copy($path, $filepath, false, FS_CHMOD_FILE)) { return 0; }

	$filetype = wp_check_filetype(basename($filepath), null);
	$attachment = array(
		'guid'           => $upload_dir['url'].'/'.$filename,
		'post_mime_type' => $filetype['type'],
		'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
		'post_content'   => '',
		'post_status'    => 'inherit'
	);

	$attach_id = wp_insert_attachment($attachment, $filepath);

	require_once(ABSPATH.'wp-admin/includes/image.php');
	$attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
	wp_update_attachment_metadata($attach_id, $attach_data);

	return $attach_id;
}



/*---------------------------------------------------------*/
/* Importers                                               */
/*---------------------------------------------------------*/

function mbt_get_importers() {
	return apply_filters('mbt_importers', array());
}

function mbt_add_disabled_importers($importers) {
	$importers['amazon'] = array(
		'name' => __('Amazon Bulk Book Importer', 'mybooktable'),
		'desc' => __('Import your books in bulk from Amazon with a list of ISBNs.', 'mybooktable'),
		'disabled' => mbt_get_upgrade_message(null, '<a href="http://www.authormedia.com/mybooktable/upgrades" target="_blank">'.__('Upgrade your MyBookTable to enable these advanced features!', 'mybooktable').'</a>'),
	);
	$importers['uiee'] = array(
		'name' => __('UIEE File', 'mybooktable'),
		'desc' => __('Import your books from a UIEE (Universal Information Exchange Environment) File.', 'mybooktable'),
		'disabled' => mbt_get_upgrade_message(null, '<a href="http://www.authormedia.com/mybooktable/upgrades" target="_blank">'.__('Upgrade your MyBookTable to enable these advanced features!', 'mybooktable').'</a>'),
	);
	return $importers;
}
add_filter('mbt_importers', 'mbt_add_disabled_importers', 9);

function mbt_import_book($book) {
	$defaults = array(
		'source_id' => null,
		'title' => '',
		'content' => '',
		'excerpt' => '',
		'authors' => array(),
		'series' => array(),
		'genres' => array(),
		'tags' => array(),
		'price' => '',
		'unique_id' => '',
		'buybuttons' => '',
		'publisher_name'  => '',
		'publication_year' => '',
		'image_id' => '',
		'imported_book_id' => '',
	);
	$book = array_merge($defaults, $book);

	if(!empty($book['imported_book_id']) and ($imported_book = get_post($book['imported_book_id']))) {
		$post_id = wp_update_post(array(
			'ID' => $imported_book->ID,
			'post_title' => $book['title'],
		));
		$old_buybuttons = get_post_meta($post_id, 'mbt_buybuttons', true);
		if(empty($old_buybuttons)) { update_post_meta($post_id, 'mbt_buybuttons', $book['buybuttons']); }
		if(!empty($book['image_id'])) { update_post_meta($post_id, 'mbt_book_image_id', $book['image_id']); }
		if(!empty($book['price'])) { update_post_meta($post_id, 'mbt_price', $book['price']); }
		if(!empty($book['unique_id'])) { update_post_meta($post_id, 'mbt_unique_id', $book['unique_id']); }
		if(!empty($book['publisher_name'])) { update_post_meta($post_id, 'mbt_publisher_name', $book['publisher_name']); }
		if(!empty($book['publication_year'])) { update_post_meta($post_id, 'mbt_publication_year', $book['publication_year']); }
		if(!empty($book['authors'])) { wp_set_object_terms($post_id, mbt_import_taxonomy_terms($book['authors'], 'mbt_author'), 'mbt_author'); }
		if(!empty($book['series'])) { wp_set_object_terms($post_id, mbt_import_taxonomy_terms($book['series'], 'mbt_series'), 'mbt_series'); }
		if(!empty($book['genres'])) { wp_set_object_terms($post_id, mbt_import_taxonomy_terms($book['genres'], 'mbt_genre'), 'mbt_genre'); }
		if(!empty($book['tags'])) { wp_set_object_terms($post_id, mbt_import_taxonomy_terms($book['tags'], 'mbt_tag'), 'mbt_tag'); }
	} else {
		$post_id = wp_insert_post(array(
			'post_title' => $book['title'],
			'post_content' => $book['content'],
			'post_excerpt' => $book['excerpt'],
			'post_status' => 'publish',
			'post_type' => 'mbt_book'
		));
		update_post_meta($post_id, 'mbt_buybuttons', $book['buybuttons']);
		update_post_meta($post_id, 'mbt_book_image_id', $book['image_id']);
		update_post_meta($post_id, 'mbt_price', $book['price']);
		update_post_meta($post_id, 'mbt_unique_id', $book['unique_id']);
		update_post_meta($post_id, 'mbt_publisher_name', $book['publisher_name']);
		update_post_meta($post_id, 'mbt_publication_year', $book['publication_year']);
		wp_set_object_terms($post_id, mbt_import_taxonomy_terms($book['authors'], 'mbt_author'), 'mbt_author');
		wp_set_object_terms($post_id, mbt_import_taxonomy_terms($book['series'], 'mbt_series'), 'mbt_series');
		wp_set_object_terms($post_id, mbt_import_taxonomy_terms($book['genres'], 'mbt_genre'), 'mbt_genre');
		wp_set_object_terms($post_id, mbt_import_taxonomy_terms($book['tags'], 'mbt_tag'), 'mbt_tag');

		if(!empty($book['source_id'])) { update_post_meta($book['source_id'], 'mbt_imported_book_id', $post_id); }
	}

	return $post_id;
}

function mbt_import_taxonomy_terms($term_names, $taxonomy) {
	$returns = array();
	foreach($term_names as $name) {
		if(term_exists($name, $taxonomy)) {
			$new_term = (array)get_term_by('name', $name, $taxonomy);
		} else {
			$new_term = (array)wp_insert_term($name, $taxonomy);
		}
		$returns[] = $new_term['term_id'];
	}
	return $returns;
}



/*---------------------------------------------------------*/
/* Pages                                                   */
/*---------------------------------------------------------*/

function mbt_add_custom_page($name, $function, $permissions="edit_posts") {
	$add_sort_books_page = function() use ($name, $permissions, $function) {
		add_submenu_page("mbt_dashboard", "", "", $permissions, $name, $function);
	};

	$remove_sort_books_page = function() use ($name) {
		remove_submenu_page("mbt_dashboard", $name);
	};

	add_action('admin_menu', $add_sort_books_page, 9);
	add_action('admin_head', $remove_sort_books_page);
}

function mbt_get_custom_page_url($name) {
	return admin_url('admin.php?page='.$name);
}



/*---------------------------------------------------------*/
/* Styles                                                  */
/*---------------------------------------------------------*/

function mbt_image_url($image) {
	$url = mbt_current_style_url($image);
	return apply_filters('mbt_image_url', empty($url) ? plugins_url('styles/Default/'.$image, dirname(__FILE__)) : $url, $image);
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

function mbt_add_uploaded_style_folder($folders) {
	$upload_dir = wp_upload_dir();
	$folders[] = array('dir' => $upload_dir['basedir'].DIRECTORY_SEPARATOR.'mbt_styles', 'url' => $upload_dir['baseurl'].'/'.'mbt_styles');
	return $folders;
}
add_filter('mbt_style_folders', 'mbt_add_uploaded_style_folder', 100);



/*---------------------------------------------------------*/
/* Tracking                                                */
/*---------------------------------------------------------*/

function mbt_init_tracking() {
	if(mbt_get_setting('allow_tracking') !== 'yes') { return; }

	if(!wp_next_scheduled('mbt_periodic_tracking')) { wp_schedule_event(time(), 'daily', 'mbt_periodic_tracking'); }
	add_action('mbt_periodic_tracking', 'mbt_send_tracking_data');
}
add_action('mbt_init', 'mbt_init_tracking');

function mbt_load_tracking_data() {
	global $mbt_tracking_data;
	if(empty($mbt_tracking_data)) {
		mt_srand(time());
		$mbt_tracking_data = get_option('mbt_tracking_data');
		if(empty($mbt_tracking_data)) {
			$id = hash('sha256', strval(get_bloginfo('url')).strval(time()).strval(rand()));

			$mbt_tracking_data = array(
				'id' => $id,
				'events' => array(),
				'ab_status' => array(),
			);

			update_option('mbt_tracking_data', $mbt_tracking_data);
		}
	}
}

function mbt_get_tracking_data($name) {
	global $mbt_tracking_data;
	mbt_load_tracking_data();
	return isset($mbt_tracking_data[$name]) ? $mbt_tracking_data[$name] : NULL;
}

function mbt_update_tracking_data($name, $value) {
	global $mbt_tracking_data;
	mbt_load_tracking_data();
	$mbt_tracking_data[$name] = $value;
	update_option('mbt_tracking_data', $mbt_tracking_data);
}

function mbt_track_event($name, $instance=false) {
	if(mbt_get_setting('allow_tracking') !== 'yes') { return; }

	$events = mbt_get_tracking_data('events');
	$events[$name]['count'] += 1;
	$events[$name]['last_time'] = time();

	if($instance !== false) {
		if(!is_array($instance)) { $instance = array(); }
		$instance['time'] = time();
		$instance['version'] = MBT_VERSION;
		$events[$name]['instances'][] = $instance;
	}

	mbt_update_tracking_data('events', $events);
}

function mbt_send_tracking_data() {
	if(mbt_get_setting('allow_tracking') !== 'yes') { return; }

	$books_query = new WP_Query(array('post_type' => 'mbt_book'));
	$books = $books_query->posts;

	$book_metas = array_map(create_function('$post', 'return get_post_custom($post->ID);'), $books);
	$num_sample_chapters = 0;
	$num_prices = 0;
	$num_isbns = 0;
	$num_publisher_names = 0;
	foreach($book_metas as $meta) {
		if(!empty($meta['mbt_sample_url'][0])) { $num_sample_chapters++; }
		if(!empty($meta['mbt_price'][0])) { $num_prices++; }
		if(!empty($meta['mbt_unique_id'][0])) { $num_isbns++; }
		if(!empty($meta['mbt_publisher_name'][0])) { $num_publisher_names++; }
	}

	$stores = mbt_get_stores();
	$buybuttons_stats = array();
	foreach($book_metas as $meta) {
		if(!empty($meta['mbt_buybuttons'][0])) {
			$buybuttons = maybe_unserialize($meta['mbt_buybuttons'][0]);
			if(is_array($buybuttons)) {
				foreach($buybuttons as $buybuttons) {
					$buybuttons_stats[$buybutton['store']]++;
				}
			}
		}
	}

	$data = array(
		'id' => mbt_get_tracking_data('id'),
		'time' => time(),
		'version' => MBT_VERSION,
		'settings' => array(
			'installed_examples' => mbt_get_setting('installed_examples'),
			'compatibility_mode' => mbt_get_setting('compatibility_mode'),
			'style_pack' => mbt_get_setting('style_pack'),
			'image_size' => mbt_get_setting('image_size'),
			'reviews_box' => mbt_get_setting('reviews_box'),
			'enable_socialmedia_badges_single_book' => mbt_get_setting('enable_socialmedia_badges_single_book'),
			'enable_socialmedia_badges_book_excerpt' => mbt_get_setting('enable_socialmedia_badges_book_excerpt'),
			'enable_socialmedia_bar_single_book' => mbt_get_setting('enable_socialmedia_bar_single_book'),
			'enable_seo' => mbt_get_setting('enable_seo'),
			'enable_buybutton_shadowbox' => mbt_get_setting('enable_buybutton_shadowbox'),
			'enable_breadcrumbs' => mbt_get_setting('enable_breadcrumbs'),
			'show_series' => mbt_get_setting('show_series'),
			'book_button_size' => mbt_get_setting('book_button_size'),
			'listing_button_size' => mbt_get_setting('listing_button_size'),
			'widget_button_size' => mbt_get_setting('widget_button_size'),
			'posts_per_page' => mbt_get_setting('posts_per_page'),
			'enable_default_affiliates' => (mbt_get_setting('enable_default_affiliates') or mbt_get_upgrade()),
			'product_name' => mbt_get_setting('product_name'),
			'hide_domc_notice' => mbt_get_setting('hide_domc_notice'),
		),
		'upgrade' => array(
			'name' => mbt_get_upgrade(),
			'version' => mbt_get_upgrade_version(),
			'settings' => array(
				'disable_amazon_affiliates' => mbt_get_setting('disable_amazon_affiliates'),
				'disable_linkshare_affiliates' => mbt_get_setting('disable_linkshare_affiliates'),
				'disable_cj_affiliates' => mbt_get_setting('disable_cj_affiliates'),
				'enable_gridview' => mbt_get_setting('enable_gridview'),
			)
		),
		'statistics' => array(
			'num_books' => count($books),
			'num_sample_chapters' => $num_sample_chapters,
			'num_prices' => $num_prices,
			'num_isbns' => $num_isbns,
			'num_publisher_names' => $num_publisher_names,
			'buybuttons' => $buybuttons_stats,
		),
		'events' => mbt_get_tracking_data('events'),
		'ab_status' => mbt_get_tracking_data('ab_status'),
	);

	$options = array(
		'timeout' => ((defined('DOING_CRON') && DOING_CRON) ? 30 : 3),
		'body' => $data,
		'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url')
	);

	$response = wp_remote_post('http://api.authormedia.com/plugins/mybooktable/analytics/submit', $options);
}

function mbt_get_ab_testing_status($name=false, $options=array(true,false)) {
	$ab_status = mbt_get_tracking_data('ab_status');
	if(!isset($ab_status[$name])) {
		$i = mt_rand(0, count($options)-1);
		$ab_status[$name] = $options[$i];
		mbt_update_tracking_data('ab_status', $ab_status);
	}
	return $ab_status[$name];
}



/*---------------------------------------------------------*/
/* API / Upgrades                                          */
/*---------------------------------------------------------*/

function mbt_verify_api_key() {
	global $wp_version;

	$to_send = array(
		'action' => 'basic_check',
		'version' => MBT_VERSION,
		'api-key' => mbt_get_setting('api_key'),
		'site' => get_bloginfo('url')
	);

	$options = array(
		'timeout' => 3,
		'body' => $to_send,
		'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url')
	);

	$raw_response = wp_remote_post('http://api.authormedia.com/plugins/apikey/check', $options);

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

	if($status > 10) {
		mbt_update_setting('api_key_status', $status);
		$expires = empty($response['expires']) ? '' : ' Expires '.date('F j, Y', $response['expires']).'.';

		$permissions = array();
		if(!empty($response['permissions']) and is_array($response['permissions'])) {
			$permissions = $response['permissions'];
		}

		if(in_array('mybooktable-pro', $permissions)) {
			mbt_update_setting('upgrade_active', 'mybooktable-pro');
			mbt_update_setting('api_key_message', __('Valid for MyBookTable Professional 1.0', 'mybooktable').$expires);

			//deprecated legacy functionality
			mbt_update_setting('pro_active', true);
			mbt_update_setting('dev_active', false);
		}
		if(in_array('mybooktable-dev', $permissions)) {
			mbt_update_setting('upgrade_active', 'mybooktable-dev');
			mbt_update_setting('api_key_message', __('Valid for MyBookTable Developer 1.0', 'mybooktable').$expires);

			//deprecated legacy functionality
			mbt_update_setting('pro_active', true);
			mbt_update_setting('dev_active', true);
		}
		if(in_array('mybooktable-pro2', $permissions)) {
			mbt_update_setting('upgrade_active', 'mybooktable-pro2');
			mbt_update_setting('api_key_message', __('Valid for MyBookTable Professional 2.0', 'mybooktable').$expires);
		}
		if(in_array('mybooktable-dev2', $permissions)) {
			mbt_update_setting('upgrade_active', 'mybooktable-dev2');
			mbt_update_setting('api_key_message', __('Valid for MyBookTable Developer 2.0', 'mybooktable').$expires);
		}
	} else if($status == -10) {
		mbt_update_setting('api_key_status', $status);
		mbt_update_setting('api_key_message', __('Key not found', 'mybooktable'));
		mbt_update_setting('upgrade_active', false);
	} else if($status == -11) {
		mbt_update_setting('api_key_status', $status);
		mbt_update_setting('api_key_message', __('Key has been deactivated', 'mybooktable'));
		mbt_update_setting('upgrade_active', false);
	} else if($status == -12) {
		mbt_update_setting('api_key_status', $status);
		mbt_update_setting('api_key_message', __('Key has expired. Please renew your license.', 'mybooktable'));
		mbt_update_setting('upgrade_active', false);
	} else {
		mbt_update_setting('api_key_status', -2);
		mbt_update_setting('api_key_message', __('Invalid response received from server', 'mybooktable'));
		return;
	}
}

function mbt_init_api_key_check() {
	if(!wp_next_scheduled('mbt_periodic_api_key_check')) { wp_schedule_event(time(), 'daily', 'mbt_periodic_api_key_check'); }
	add_action('mbt_periodic_api_key_check', 'mbt_verify_api_key');
}
add_action('mbt_init', 'mbt_init_api_key_check');

function mbt_get_upgrade() {
	$upgrade_active = mbt_get_setting('upgrade_active');
	return empty($upgrade_active) ? false : $upgrade_active;
}

function mbt_get_upgrade_version() {
	$upgrade = mbt_get_upgrade();
	if($upgrade == 'mybooktable-dev2' and defined('MBTDEV2_VERSION')) { return MBTDEV2_VERSION; }
	if($upgrade == 'mybooktable-pro2' and defined('MBTPRO2_VERSION')) { return MBTPRO2_VERSION; }
	if($upgrade == 'mybooktable-dev' and defined('MBTDEV_VERSION')) { return MBTDEV_VERSION; }
	if($upgrade == 'mybooktable-pro' and defined('MBTPRO_VERSION')) { return MBTPRO_VERSION; }
	return false;
}

function mbt_get_upgrade_plugin_exists($active=true) {
	if(!$active) { return defined('MBTDEV2_VERSION') or defined('MBTPRO2_VERSION') or defined('MBTDEV_VERSION') or defined('MBTPRO_VERSION'); }
	$upgrade = mbt_get_upgrade();
	if($upgrade == 'mybooktable-dev2') { return defined('MBTDEV2_VERSION'); }
	if($upgrade == 'mybooktable-pro2') { return defined('MBTPRO2_VERSION'); }
	if($upgrade == 'mybooktable-dev')  { return defined('MBTDEV_VERSION'); }
	if($upgrade == 'mybooktable-pro')  { return defined('MBTPRO_VERSION'); }
	return false;
}

function mbt_get_upgrade_message($upgrade_text=null, $thankyou_text=null) {
	if(mbt_get_upgrade()) {
		if(mbt_get_upgrade_plugin_exists()) {
			return ($thankyou_text !== null ? $thankyou_text : (__('Thank you for purchasing a MyBookTable Upgrade!', 'mybooktable').' <a href="http://authormedia.freshdesk.com/support/home" target="_blank">'.__('Get premium support.', 'mybooktable').'</a>'));
		} else {
			return '<a href="'.admin_url('admin.php?page=mbt_dashboard&subpage=mbt_get_upgrade_page').'">'.__('Download your MyBookTable Upgrade plugin to enable your advanced features!', 'mybooktable').'</a>';
		}
	} else {
		if(mbt_get_upgrade_plugin_exists(false)) {
			$api_key = mbt_get_setting('api_key');
			if(empty($api_key)) {
				return '<a href="'.admin_url('admin.php?page=mbt_settings').'">'.__('Insert your API Key to enable your advanced features!', 'mybooktable').'</a>';
			} else {
				return '<a href="'.admin_url('admin.php?page=mbt_settings').'">'.__('Update your API Key to enable your advanced features!', 'mybooktable').'</a>';
			}
		} else {
			return '<a href="http://www.authormedia.com/mybooktable/upgrades" target="_blank">'.($upgrade_text !== null ? $upgrade_text : __('Upgrade your MyBookTable to enable these advanced features!', 'mybooktable')).'</a>';
		}
	}
}
