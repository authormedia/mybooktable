<?php

function mbt_templates_init() {
	//register image size
	list($width, $height) = apply_filters('mbt_book_image_size', array(400, 400));
	add_image_size('mbt_book_image', $width, $height, false);

	if(!is_admin()) {
		//enqueue frontend styling
		add_action('wp_enqueue_scripts', 'mbt_enqueue_resources');
		add_action('wp_head', 'mbt_add_custom_css');

		//modify the post query
		add_action('pre_get_posts', 'mbt_pre_get_posts', 20);

		//override page template
		add_filter('template_include', 'mbt_load_book_templates');

		//add body tag
		add_filter('body_class', 'mbt_body_class');

		//general hooks
		add_action('mbt_content_wrapper_start', 'mbt_do_wrapper_start');
		add_action('mbt_content_wrapper_end', 'mbt_do_wrapper_end');
		add_action('mbt_book_excerpt', 'mbt_do_book_excerpt');

		//book archive hooks
		add_action('mbt_book_archive_content', 'mbt_do_book_archive_content');
		add_action('mbt_before_book_archive', 'mbt_do_before_book_archive', 0);
		add_action('mbt_after_book_archive', 'mbt_do_after_book_archive', 20);
		add_action('mbt_book_archive_header', 'mbt_do_book_archive_header');
		add_action('mbt_book_archive_header_image', 'mbt_do_book_archive_header_image');
		add_action('mbt_book_archive_header_title', 'mbt_do_book_archive_header_title');
		add_action('mbt_book_archive_header_description', 'mbt_do_book_archive_header_description');
		add_action('mbt_book_archive_loop', 'mbt_do_book_archive_loop');
		add_action('mbt_book_archive_no_results', 'mbt_do_book_archive_no_results');
		add_action('mbt_after_book_archive_loop', 'mbt_the_book_archive_pagination');

		//single book hooks
		add_action('mbt_single_book_content', 'mbt_do_single_book_content');
		add_action('mbt_before_single_book', 'mbt_do_before_single_book', 0);
		add_action('mbt_after_single_book', 'mbt_do_after_single_book', 20);
		add_action('mbt_single_book_images', 'mbt_do_single_book_images');
		add_action('mbt_single_book_title', 'mbt_do_single_book_title');
		add_action('mbt_single_book_price', 'mbt_do_single_book_price');
		add_action('mbt_single_book_meta', 'mbt_do_single_book_meta');
		add_action('mbt_single_book_blurb', 'mbt_do_single_book_blurb');
		add_action('mbt_single_book_buybuttons', 'mbt_do_single_book_buybuttons');
		add_action('mbt_single_book_overview', 'mbt_do_single_book_overview');
		add_action('mbt_after_single_book', 'mbt_the_reviews_box', 15);
		if(!mbt_get_setting('hide_domc_notice')) { add_action('mbt_after_single_book', 'mbt_the_domc_notice', 6); }
		if(mbt_get_setting('show_series')) { add_action('mbt_after_single_book', 'mbt_the_book_series_box'); }
		if(mbt_get_setting('show_find_bookstore')) { add_action('mbt_after_single_book', 'mbt_the_find_bookstore_box'); }

		//book excerpt hooks
		add_action('mbt_before_book_excerpt', 'mbt_do_before_book_excerpt', 0);
		add_action('mbt_after_book_excerpt', 'mbt_do_after_book_excerpt', 20);
		add_action('mbt_book_excerpt_images', 'mbt_do_book_excerpt_images');
		add_action('mbt_book_excerpt_title', 'mbt_do_book_excerpt_title');
		add_action('mbt_book_excerpt_price', 'mbt_do_book_excerpt_price');
		add_action('mbt_book_excerpt_meta', 'mbt_do_book_excerpt_meta');
		add_action('mbt_book_excerpt_blurb', 'mbt_do_book_excerpt_blurb');
		add_action('mbt_book_excerpt_buybuttons', 'mbt_do_book_excerpt_buybuttons');

		//social media hooks
		if(mbt_get_setting('enable_socialmedia_badges_single_book')) { add_action('mbt_single_book_title', 'mbt_do_single_book_socialmedia_badges', 5); }
		if(mbt_get_setting('enable_socialmedia_badges_book_excerpt')) { add_action('mbt_book_excerpt_title', 'mbt_do_book_excerpt_socialmedia_badges', 5); }
		if(mbt_get_setting('enable_socialmedia_bar_single_book')) { add_action('mbt_single_book_overview', 'mbt_do_single_book_socialmedia_bar', 20); }
	}
}
add_action('mbt_init', 'mbt_templates_init');



/*---------------------------------------------------------*/
/* Template Overload Functions                             */
/*---------------------------------------------------------*/
function mbt_load_book_templates($template) {
	if(mbt_is_booktable_page() or is_post_type_archive('mbt_book') or is_tax('mbt_author') or is_tax('mbt_genre') or is_tax('mbt_series') or is_tax('mbt_tag')) {
		$template = mbt_locate_template('archive-book.php');
	} else if(is_singular('mbt_book')) {
		$template = mbt_locate_template('single-book.php');
	}

	return $template;
}

function mbt_pre_get_posts($query) {
	if(!is_admin() and $query->is_main_query()) {
		if(mbt_get_setting('booktable_page') and ($booktable_page = get_post(mbt_get_setting('booktable_page')))) {
			if($query->is_post_type_archive('mbt_book')) {
				$paged = $query->get('paged');
				$query->init();
				$query->set('page_id', $booktable_page->ID);
				$query->set('paged', $paged);
				$query->parse_query();
			}
			if($query->is_page() and ($query->get('page_id') == $booktable_page->ID or $query->get('pagename') == $booktable_page->post_name)) {
				global $mbt_is_booktable_page;
				$mbt_is_booktable_page = true;
				$query->set('paged', $query->get('paged') ? $query->get('paged') : get_query_var('page'));
				add_action('mbt_before_book_archive', 'mbt_do_before_booktable_page');
				add_action('mbt_after_book_archive', 'mbt_do_after_booktable_page');
			}
		}
		if($query->is_post_type_archive('mbt_book') or $query->is_tax('mbt_author') or $query->is_tax('mbt_genre') or $query->is_tax('mbt_tag')) {
			$query->set('orderby', 'menu_order');
			$query->set('posts_per_page', mbt_get_posts_per_page());
		}
		if($query->is_tax('mbt_series')) {
			$query->set('orderby', 'meta_value_num');
			$query->set('meta_key', 'mbt_series_order');
			$query->set('order', 'ASC');
			$query->set('posts_per_page', mbt_get_posts_per_page());
		}
	}
}

function mbt_enqueue_resources() {
	wp_enqueue_style('mbt-style', plugins_url('css/frontend-style.css', dirname(__FILE__)), array(), MBT_VERSION);
	$plugin_style_css = mbt_current_style_url('style.css');
	if(!empty($plugin_style_css)) { wp_enqueue_style('mbt-style-pack', $plugin_style_css, array(), MBT_VERSION); }

	wp_enqueue_script('mbt-frontend-js', plugins_url('js/frontend.js', dirname(__FILE__)), array('jquery'), MBT_VERSION);
	wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp', array(), MBT_VERSION);
	wp_enqueue_script('mbt-shadowbox', plugins_url('js/lib/jquery.colorbox.min.js', dirname(__FILE__)), array('jquery'), MBT_VERSION);
}

function mbt_get_template_folders() {
	return apply_filters('mbt_template_folders', array());
}

function mbt_add_default_template_folder($folders) {
	$folders[] = plugin_dir_path(dirname(__FILE__)).'templates/';
	return $folders;
}
add_filter('mbt_template_folders', 'mbt_add_default_template_folder', 100);

function mbt_add_theme_template_folders($folders) {
	$folders[] = get_stylesheet_directory().'/mybooktable/';
	$folders[] = get_template_directory().'/mybooktable/';
	return $folders;
}
add_filter('mbt_template_folders', 'mbt_add_theme_template_folders', 50);

function mbt_locate_template($name) {
	$template_folders = mbt_get_template_folders();
	foreach($template_folders as $folder) {
		$locatedtemplate = $folder.$name;
		if(file_exists($locatedtemplate)) { break; }
	}
	return $locatedtemplate;
}

function mbt_include_template($name) {
	$locatedtemplate = mbt_locate_template($name);
	if(file_exists($locatedtemplate)) { include($locatedtemplate); }
}

function mbt_add_custom_css() {
	$image_size = mbt_get_setting('image_size');
	$book_button_size = mbt_get_setting('book_button_size');
	$listing_button_size = mbt_get_setting('listing_button_size');
	$widget_button_size = mbt_get_setting('widget_button_size');
	echo('<style type="text/css">');
	//Image Size
	if($image_size == 'small') { echo('.mbt-book .mbt-book-images { width: 15%; } .mbt-book .mbt-book-right { width: 85%; } '); }
	else if($image_size == 'large') { echo('.mbt-book .mbt-book-images { width: 35%; } .mbt-book .mbt-book-right { width: 65%; } '); }
	else { echo('.mbt-book .mbt-book-images { width: 25%; } .mbt-book .mbt-book-right { width: 75%; } '); }
	//Book Button Size
	if($book_button_size == 'small') { echo('.mbt-book .mbt-book-buybuttons .mbt-book-buybutton img { width: 144px; height: 25px; } .mbt-book .mbt-book-buybuttons .mbt-book-buybutton { padding: 3px 6px 0px 0px; }'); }
	else if($book_button_size == 'medium') { echo('.mbt-book .mbt-book-buybuttons .mbt-book-buybutton img { width: 172px; height: 30px; } .mbt-book .mbt-book-buybuttons .mbt-book-buybutton { padding: 4px 8px 0px 0px; }'); }
	else { echo('.mbt-book .mbt-book-buybuttons .mbt-book-buybutton img { width: 201px; height: 35px; } .mbt-book .mbt-book-buybuttons .mbt-book-buybutton { padding: 5px 10px 0px 0px; }'); }
	//Listing Button Size
	if($listing_button_size == 'small') { echo('.mbt-book-archive .mbt-book .mbt-book-buybuttons .mbt-book-buybutton img { width: 144px; height: 25px; } .mbt-book-archive .mbt-book .mbt-book-buybuttons .mbt-book-buybutton { padding: 3px 6px 0px 0px; }'); }
	else if($listing_button_size == 'medium') { echo('.mbt-book-archive .mbt-book .mbt-book-buybuttons .mbt-book-buybutton img { width: 172px; height: 30px; } .mbt-book-archive .mbt-book .mbt-book-buybuttons .mbt-book-buybutton { padding: 4px 8px 0px 0px; }'); }
	else { echo('.mbt-book-archive .mbt-book .mbt-book-buybuttons .mbt-book-buybutton img { width: 201px; height: 35px; } .mbt-book-archive .mbt-book .mbt-book-buybuttons .mbt-book-buybutton { padding: 5px 10px 0px 0px; }'); }
	//Widget Button Size
	if($widget_button_size == 'small') { echo('.mbt-featured-book-widget .mbt-book-buybuttons .mbt-book-buybutton img { width: 144px; height: 25px; } .mbt-featured-book-widget .mbt-book-buybuttons .mbt-book-buybutton { padding: 3px 6px 0px 0px; }'); }
	else if($widget_button_size == 'medium') { echo('.mbt-featured-book-widget .mbt-book-buybuttons .mbt-book-buybutton img { width: 172px; height: 30px; } .mbt-featured-book-widget .mbt-book-buybuttons .mbt-book-buybutton { padding: 4px 8px 0px 0px; }'); }
	else { echo('.mbt-featured-book-widget .mbt-book-buybuttons .mbt-book-buybutton img { width: 201px; height: 35px; } .mbt-featured-book-widget .mbt-book-buybuttons .mbt-book-buybutton { padding: 5px 10px 0px 0px; }'); }
	echo('</style>');
}

function mbt_body_class($classes) {
	if(mbt_is_mbt_page()) {
		$classes[] = 'mybooktable';
	}
	return $classes;
}



/*---------------------------------------------------------*/
/* General Template Functions                              */
/*---------------------------------------------------------*/
function mbt_do_wrapper_start() {
	mbt_include_template("wrapper-start.php");
}
function mbt_do_wrapper_end() {
	mbt_include_template("wrapper-end.php");
}
function mbt_do_book_excerpt() {
	mbt_include_template("excerpt-book.php");
}
function mbt_do_before_booktable_page() {
	global $wp_query, $posts, $post, $id, $mbt_old_wp_query, $mbt_old_posts, $mbt_old_post, $mbt_old_id;
	if($wp_query->is_main_query()) {
		$mbt_old_wp_query = $wp_query;
		$mbt_old_posts = $posts;
		$mbt_old_post = $post;
		$mbt_old_id = $id;
		$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'paged' => $mbt_old_wp_query->get('paged'), 'orderby' => 'menu_order', 'posts_per_page' => mbt_get_posts_per_page()));
	}
}
function mbt_do_after_booktable_page() {
	global $wp_query, $posts, $post, $id, $mbt_old_wp_query, $mbt_old_posts, $mbt_old_post, $mbt_old_id;
	if($mbt_old_wp_query) {
		$wp_query = $mbt_old_wp_query;
		$posts = $mbt_old_posts;
		$post = $mbt_old_post;
		$id = $mbt_old_id;
	}
}


/*---------------------------------------------------------*/
/* Book Archive Template Functions                         */
/*---------------------------------------------------------*/
function mbt_do_book_archive_content() {
	mbt_include_template("archive-book/content.php");
}
function mbt_do_before_book_archive() {
	mbt_include_template("archive-book/before.php");
}
function mbt_do_after_book_archive() {
	mbt_include_template("archive-book/after.php");
}
function mbt_do_book_archive_header() {
	mbt_include_template("archive-book/header.php");
}
function mbt_do_book_archive_header_image() {
	mbt_include_template("archive-book/header/image.php");
}
function mbt_do_book_archive_header_title() {
	mbt_include_template("archive-book/header/title.php");
}
function mbt_do_book_archive_header_description() {
	mbt_include_template("archive-book/header/description.php");
}
function mbt_do_book_archive_loop() {
	mbt_include_template("archive-book/loop.php");
}
function mbt_do_book_archive_no_results() {
	mbt_include_template("archive-book/no-results.php");
}



/*---------------------------------------------------------*/
/* Single Book Template Functions                          */
/*---------------------------------------------------------*/
function mbt_do_single_book_content() {
	mbt_include_template("single-book/content.php");
}
function mbt_do_before_single_book() {
	mbt_include_template("single-book/before.php");
}
function mbt_do_after_single_book() {
	mbt_include_template("single-book/after.php");
}
function mbt_do_single_book_images() {
	mbt_include_template("single-book/images.php");
}
function mbt_do_single_book_title() {
	mbt_include_template("single-book/title.php");
}
function mbt_do_single_book_price() {
	mbt_include_template("single-book/price.php");
}
function mbt_do_single_book_meta() {
	mbt_include_template("single-book/meta.php");
}
function mbt_do_single_book_blurb() {
	mbt_include_template("single-book/blurb.php");
}
function mbt_do_single_book_buybuttons() {
	mbt_include_template("single-book/buybuttons.php");
}
function mbt_do_single_book_overview() {
	mbt_include_template("single-book/overview.php");
}
function mbt_do_single_book_socialmedia_badges() {
	mbt_include_template("single-book/socialmedia-badges.php");
}
function mbt_do_single_book_socialmedia_bar() {
	mbt_include_template("single-book/socialmedia-bar.php");
}



/*---------------------------------------------------------*/
/* Book Excerpt Template Functions                         */
/*---------------------------------------------------------*/
function mbt_do_before_book_excerpt() {
	mbt_include_template("excerpt-book/before.php");
}
function mbt_do_after_book_excerpt() {
	mbt_include_template("excerpt-book/after.php");
}
function mbt_do_book_excerpt_images() {
	mbt_include_template("excerpt-book/images.php");
}
function mbt_do_book_excerpt_title() {
	mbt_include_template("excerpt-book/title.php");
}
function mbt_do_book_excerpt_price() {
	mbt_include_template("excerpt-book/price.php");
}
function mbt_do_book_excerpt_meta() {
	mbt_include_template("excerpt-book/meta.php");
}
function mbt_do_book_excerpt_blurb() {
	mbt_include_template("excerpt-book/blurb.php");
}
function mbt_do_book_excerpt_buybuttons() {
	mbt_include_template("excerpt-book/buybuttons.php");
}
function mbt_do_book_excerpt_socialmedia_badges() {
	mbt_include_template("excerpt-book/socialmedia-badges.php");
}



/*---------------------------------------------------------*/
/* General Book Template Functions                         */
/*---------------------------------------------------------*/

function mbt_get_book_archive_image() {
	$query_obj = get_queried_object();
	if(empty($query_obj) or empty($query_obj->taxonomy)) { return ''; }
	$img = mbt_get_taxonomy_image($query_obj->taxonomy, $query_obj->term_id);
	return apply_filters('mbt_get_book_archive_image', empty($img) ? '' : '<img class="mbt-book-archive-image" src="'.$img.'">');
}
function mbt_the_book_archive_image() {
	echo(mbt_get_book_archive_image());
}

function mbt_get_book_archive_title($before = '', $after = '') {
	$output = '';

	if(is_tax('mbt_author')) {
		$output = __('Author', 'mybooktable').': '.get_queried_object()->name;
	} else if(is_tax('mbt_genre')) {
		$output = __('Genre', 'mybooktable').': '.get_queried_object()->name;
	} else if(is_tax('mbt_series')) {
		$output = __('Series', 'mybooktable').': '.get_queried_object()->name;
	} else if(is_tax('mbt_tag')) {
		$output = __('Tag', 'mybooktable').': '.get_queried_object()->name;
	} else if(mbt_is_booktable_page()) {
		$booktable_page = get_post(mbt_get_setting('booktable_page'));
		$output = $booktable_page->post_title;
	} else if(is_post_type_archive('mbt_book')) {
		if(mbt_get_setting('booktable_page') and ($booktable_page = get_post(mbt_get_setting('booktable_page')))) {
			$output = $booktable_page->post_title;
		} else {
			$output = mbt_get_product_name();
		}
	}

	return apply_filters('mbt_get_book_archive_title', empty($output) ? '' : $before.$output.$after, $before, $after);
}
function mbt_the_book_archive_title($before = '', $after = '') {
	echo(mbt_get_book_archive_title($before, $after));
}

function mbt_get_book_archive_description($before = '', $after = '') {
	$output = '';

	if(is_tax('mbt_author') or is_tax('mbt_genre') or is_tax('mbt_series') or is_tax('mbt_tag')) {
		$output = apply_filters('term_description', get_queried_object()->description);
	} else if(mbt_is_booktable_page()) {
		$booktable_page = get_post(mbt_get_setting('booktable_page'));
		if(function_exists('st_remove_st_add_link')) { st_remove_st_add_link(''); }
		$output = apply_filters('the_content', $booktable_page->post_content);
	} else if(is_post_type_archive('mbt_book')) {
		if(mbt_get_setting('booktable_page') and ($booktable_page = get_post(mbt_get_setting('booktable_page')))) {
			if(function_exists('st_remove_st_add_link')) { st_remove_st_add_link(''); }
			$output = apply_filters('the_content', $booktable_page->post_content);
		}
	}

	return apply_filters('mbt_get_book_archive_description', empty($output) ? '' : $before.$output.$after, $before, $after);
}
function mbt_the_book_archive_description($before = '', $after = '') {
	echo(mbt_get_book_archive_description($before, $after));
}

function mbt_get_book_archive_pagination() {
	global $wp_query;

	$posts_per_page = intval($wp_query->get('posts_per_page'));
	$paged = max(1, absint($wp_query->get('paged')));
	$total_pages = max(1, absint($wp_query->max_num_pages));
	if($total_pages < 2) { return; }

	$pages_to_show = 7;
	$pages_to_show_minus_1 = $pages_to_show - 1;
	$half_page_start = floor($pages_to_show_minus_1/2);
	$half_page_end = ceil($pages_to_show_minus_1/2);
	$start_page = max(1, $paged - $half_page_start);

	$end_page = $paged + $half_page_end;

	if(($end_page - $start_page) != $pages_to_show_minus_1) {
		$end_page = $start_page + $pages_to_show_minus_1;
	}

	if($end_page > $total_pages) {
		$start_page = max(1, $total_pages - $pages_to_show_minus_1);
		$end_page = $total_pages;
	}

	$prev_text = apply_filters('mbt_book_archive_pagination_previous', '&larr; '.__('Back', 'mybooktable'));
	$next_text = apply_filters('mbt_book_archive_pagination_next', __('More Books', 'mybooktable').' &rarr;');

	$output = '<nav class="mbt-book-archive-pagination">';

	if($paged > 1) { $output .= '<a href="'.get_pagenum_link($paged - 1).'" class="mbt-book-archive-pagination-previous">'.$prev_text.'</a>'; }
	if($start_page >= 2 && $pages_to_show < $total_pages) { $output .= '<span class="mbt-book-archive-pagination-delimiter">'.apply_filters('mbt_book_archive_pagination_delimiter', '&hellip;').'</span>'; }

	foreach(range($start_page, $end_page) as $i) {
		if($i == $paged) {
			$output .= '<span class="mbt-book-archive-pagination-page current">'.apply_filters('mbt_book_archive_pagination_page', strval($i)).'</span>';
		} else {
			$output .= '<a href="'.get_pagenum_link($i).'" class="mbt-book-archive-pagination-page">'.apply_filters('mbt_book_archive_pagination_page', strval($i)).'</a>';
		}
	}

	if($end_page < $total_pages) { $output .= '<span class="mbt-book-archive-pagination-delimiter">'.apply_filters('mbt_book_archive_pagination_delimiter', '&hellip;').'</span>'; }
	if($paged < $total_pages) { $output .= '<a href="'.get_pagenum_link($paged + 1).'" class="mbt-book-archive-pagination-next">'.$next_text.'</a>'; }

	$output .= "</nav>";

	return apply_filters('mbt_get_book_archive_pagination', $output);
}
function mbt_the_book_archive_pagination() {
	echo(mbt_get_book_archive_pagination());
}



function mbt_get_placeholder_image_src() {
	return apply_filters('mbt_get_placeholder_image_src', array(plugins_url('images/book-placeholder.jpg', dirname(__FILE__)), 400, 472));
}
function mbt_get_book_image_src($post_id) {
	//prevent Jetpack Photon from breaking image width/height by disabling their image downsize
	add_filter('jetpack_photon_override_image_downsize', '__return_true');
	$image = wp_get_attachment_image_src(get_post_meta($post_id, 'mbt_book_image_id', true), 'mbt_book_image');
	remove_filter('jetpack_photon_override_image_downsize', '__return_true');
	return apply_filters('mbt_get_book_image_src', $image ? $image : mbt_get_placeholder_image_src());
}
function mbt_get_book_image($post_id, $attrs = '') {
	list($src, $width, $height) = mbt_get_book_image_src($post_id);
	$attrs = wp_parse_args($attrs, array('alt' => wp_strip_all_tags(get_the_title($post_id)), 'class' => ''));
	$attrs['class'] .= ' mbt-book-image';
	$attributes = array();
	foreach($attrs as $attr => $value) {
		$attributes[] = $attr.'="'.$value.'"';
	}
	return apply_filters('mbt_get_book_image', '<img itemprop="image" src="'.$src.'" '.implode($attributes, ' ').'>');
}
function mbt_the_book_image($attrs = '') {
	global $post;
	echo(mbt_get_book_image($post->ID, $attrs));
}



function mbt_get_book_price($post_id) {
	$price = get_post_meta($post_id, 'mbt_price', true);
	if(preg_match("/^[0-9.]+$/", $price)) { $price =  "$".number_format((double)$price, 2); }

	$sale_price = get_post_meta($post_id, 'mbt_sale_price', true);
	if(preg_match("/^[0-9.]+$/", $sale_price)) { $sale_price =  "$".number_format((double)$sale_price, 2); }

	$output = '';
	if(!empty($sale_price) and !empty($price)) {
		$output  = '<span itemprop="offers" itemscope itemtype="http://schema.org/Offer"><span itemprop="price" class="mbt-old-price">'.$price.'</span><link itemprop="availability" href="http://schema.org/Discontinued"/></span>';
		$output .= '<span itemprop="offers" itemscope itemtype="http://schema.org/Offer"><span itemprop="price" class="mbt-new-price">'.$sale_price.'</span><link itemprop="availability" href="http://schema.org/InStock"/></span>';
	} else if(!empty($price)) {
		$output  = '<span itemprop="offers" itemscope itemtype="http://schema.org/Offer"><span itemprop="price">'.$price.'</span><link itemprop="availability" href="http://schema.org/InStock"/></span>';
	}

	return apply_filters('mbt_get_book_price', $output, $post_id);
}
function mbt_the_book_price() {
	global $post;
	echo(mbt_get_book_price($post->ID));
}



function mbt_get_book_sample_url($post_id) {
	return apply_filters('mbt_get_book_sample_url', get_post_meta($post_id, "mbt_sample_url", true));
}
function mbt_the_book_sample_url() {
	global $post;
	echo(mbt_get_book_sample_url($post->ID));
}

function mbt_get_book_sample($post_id) {
	$url = mbt_get_book_sample_url($post_id);
	return empty($url) ? '' : apply_filters('mbt_get_book_sample', '<br><a class="mbt-book-sample" target="_blank" href="'.$url.'">'.__('Download Sample Chapter', 'mybooktable').'</a>');
}
function mbt_the_book_sample() {
	global $post;
	echo(mbt_get_book_sample($post->ID));
}



function mbt_get_book_socialmedia_badges($post_id) {
	$url = urlencode(get_permalink($post_id));
	$output = '';

	$output .= '<iframe src="https://plusone.google.com/_/+1/fastbutton?url='.$url.'&amp;size=tall&amp;count=true&amp;annotation=bubble" class="mbt-gplusone" style="width: 55px; height: 61px; margin: 0px; border: none; overflow: hidden;" frameborder="0" hspace="0" vspace="0" marginheight="0" marginwidth="0" scrolling="no" allowtransparency="true"></iframe>';
	$output .= '<iframe src="http://www.facebook.com/plugins/like.php?href='.$url.'&amp;layout=box_count" class="mbt-fblike" style="width: 50px; height: 61px; margin: 0px; border: none; overflow: hidden;" scrolling="no" frameborder="0" allowtransparency="true"></iframe>';

	return apply_filters('mbt_get_book_socialmedia_badges', $output);
}
function mbt_the_book_socialmedia_badges() {
	global $post;
	echo(mbt_get_book_socialmedia_badges($post->ID));
}

function mbt_get_book_socialmedia_bar($post_id) {
	$url = urlencode(get_permalink($post_id));
	$output = '';

	if(function_exists('st_makeEntries')) {
		$output .= st_makeEntries();
	} else {
		$output .= '<iframe src="https://plusone.google.com/_/+1/fastbutton?url='.$url.'&amp;size=medium&amp;count=true&amp;annotation=bubble" class="mbt-gplusone" style="width: 75px; height: 20px; margin: 0px; border: none; overflow: hidden;" frameborder="0" hspace="0" vspace="0" marginheight="0" marginwidth="0" scrolling="no" allowtransparency="true"></iframe>';
		$output .= '<iframe src="http://www.facebook.com/plugins/like.php?href='.$url.'&amp;layout=button_count" class="mbt-fblike" style="width: 75px; height: 20px; margin: 0px; border: none; overflow: hidden;" scrolling="no" frameborder="0" allowtransparency="true"></iframe>';
		$output .= '<iframe src="http://platform.twitter.com/widgets/tweet_button.html?url='.$url.'&amp;count=horizontal&amp;size=m" class="mbt-twittershare" style="height: 20px; width: 100px; margin: 0px; border: none; overflow: hidden;" allowtransparency="true" frameborder="0" scrolling="no"></iframe>';
	}

	return apply_filters('mbt_get_book_socialmedia_bar', $output);
}
function mbt_the_book_socialmedia_bar() {
	global $post;
	echo(mbt_get_book_socialmedia_bar($post->ID));
}



function mbt_get_buybuttons($post_id, $excerpt=false, $force_shadowbox=null) {
	$output = '';
	$stores = mbt_get_stores();

	$using_shadowbox = (((($excerpt and mbt_get_setting('buybutton_shadowbox') == 'listings') or mbt_get_setting('buybutton_shadowbox') == 'all') and $force_shadowbox !== false) or $force_shadowbox === true);

	$buybuttons = mbt_query_buybuttons($post_id, array('display' => 'button'));
	if(!empty($buybuttons)) {
		foreach($buybuttons as $buybutton) {
			if(empty($stores[$buybutton['store']])) { continue; }
			$output .= mbt_format_buybutton($buybutton, $stores[$buybutton['store']]);
		}
	}

	if(!$excerpt or $using_shadowbox) {
		$textbuybuttons_output = '';
		$textbuybuttons = mbt_query_buybuttons($post_id, array('display' => 'text'));
		if(!empty($textbuybuttons)) {
			$textbuybuttons_output .= '<div class="mbt-book-buybuttons-textonly">';
			$textbuybuttons_output .= '<h3>'.__('Other book sellers', 'mybooktable').':</h3>';
			$textbuybuttons_output .= '<ul>';
			foreach($textbuybuttons as $buybutton) {
				if(empty($stores[$buybutton['store']])) { continue; }
				$textbuybuttons_output .= mbt_format_buybutton($buybutton, $stores[$buybutton['store']]);
			}
			$textbuybuttons_output .= '</ul>';
			$textbuybuttons_output .= '</div>';
		}
		$output .= apply_filters('mbt_textbuybuttons_output', $textbuybuttons_output);
	}

	$output = apply_filters('mbt_buybuttons_output', $output);

	if(!empty($output) and $using_shadowbox) {
		$book_button_size = mbt_get_setting('book_button_size');
		if($book_button_size == 'small') { $buybuttons_width = 2+150*2; }
		else if($book_button_size == 'medium') { $buybuttons_width = 2+180*2; }
		else { $buybuttons_width = 2+211*2; }

		$shadowbox_content  = apply_filters('mbt_buybuttons_shadowbox_title', '<div class="mbt-shadowbox-title">'.__('Buy This Book Online', 'mybooktable').'</div>');
		$shadowbox_content .= apply_filters('mbt_buybuttons_shadowbox_buybuttons', '<div class="mbt-book"><div class="mbt-book-buybuttons" style="width:'.$buybuttons_width.'px">'.$output.'</div></div>', $output, $buybuttons_width);
		$shadowbox_content .= apply_filters('mbt_buybuttons_shadowbox_book_image', mbt_get_book_image($post_id));
		if(mbt_get_setting('show_find_bookstore_buybuttons_shadowbox')) { $shadowbox_content .= apply_filters('mbt_buybuttons_shadowbox_find_bookstore', mbt_get_find_bookstore_box($post_id)); }
		$shadowbox_content = apply_filters('mbt_buybuttons_shadowbox_content', $shadowbox_content);

		$shadowbox_output  = '<div class="mbt-shadowbox-hidden" style="display:none"><div class="mbt-shadowbox mbt-buybuttons-shadowbox" id="mbt_buybutton_shadowbox_'.$post_id.'">';
		$shadowbox_output .= $shadowbox_content;
		$shadowbox_output .= '</div></div>';

		$shadowbox_button_output  = '<div class="mbt-book-buybutton">';
		$shadowbox_button_output .= '	<a href="#mbt_buybutton_shadowbox_'.$post_id.'" class="mbt-shadowbox-buybutton mbt-shadowbox-inline">';
		$shadowbox_button_output .= '		<img src="'.mbt_image_url('shadowbox_button.png').'" border="0" alt="'.__('Buy now!', 'mybooktable').'"/>';
		$shadowbox_button_output .= '	</a>';
		$shadowbox_button_output .= '</div>';
		$shadowbox_button_output = apply_filters('mbt_format_buybutton', $shadowbox_button_output, array('display'=>'button', 'store'=>'shadowbox', 'url'=>''), array('name' => 'Shadow Box'));

		$output = $shadowbox_output.$shadowbox_button_output;
	}

	return apply_filters('mbt_get_buybuttons', $output);
}
function mbt_the_buybuttons($excerpt=false, $force_shadowbox=null) {
	global $post;
	echo(mbt_get_buybuttons($post->ID, $excerpt, $force_shadowbox));
}



function mbt_get_book_blurb($post_id, $read_more = false) {
	$post = get_post($post_id);
	$output = $post->post_excerpt;
	if($read_more) { $output .= apply_filters('mbt_read_more', ' <a href="'.get_permalink($post_id).'" class="mbt-read-more">'.apply_filters('mbt_read_more_text',__('More info', 'mybooktable').' →' ).'</a>'); }
	return apply_filters('mbt_get_book_blurb', $output);
}
function mbt_the_book_blurb($read_more = false) {
	global $post;
	echo(mbt_get_book_blurb($post->ID, $read_more));
}



function mbt_get_book_length($post_id) {
	$length = get_post_meta($post_id, 'mbt_book_length', true);
	return empty($length) ? '' : '<span class="mbt-meta-title">'.__('Lenght', 'mybooktable').':</span> '.$length.'<br>';
}
function mbt_the_book_length() {
	global $post;
	echo(mbt_get_book_length($post->ID));
}



function mbt_get_book_publisher($post_id) {
	$publisher_name = get_post_meta($post_id, 'mbt_publisher_name', true);
	$publisher_url = get_post_meta($post_id, 'mbt_publisher_url', true);
	if(empty($publisher_name)) { return ''; }
	if(empty($publisher_url)) {
		$publisher_string = '<span class="mbt-publisher">'.$publisher_name.'</span><br>';
	} else {
		$publisher_string = '<a href="'.$publisher_url.'" target="_blank" rel="nofollow" class="mbt-publisher">'.$publisher_name.'</a><br>';
	}
	$output = '<span class="mbt-meta-title">'.__('Publisher', 'mybooktable').':</span> '.$publisher_string;
	return apply_filters('mbt_get_book_publisher', $output);
}
function mbt_the_book_publisher() {
	global $post;
	echo(mbt_get_book_publisher($post->ID));
}



function mbt_get_book_publication_year($post_id) {
	$publication_year = get_post_meta($post_id, 'mbt_publication_year', true);
	$output = empty($publication_year) ? '' : '<span class="mbt-meta-title">'.__('Publication Year', 'mybooktable').':</span> '.$publication_year.'<br>';
	return apply_filters('mbt_get_book_publication_year', $output);
}
function mbt_the_book_publication_year() {
	global $post;
	echo(mbt_get_book_publication_year($post->ID));
}



function mbt_get_book_unique_id($post_id) {
	$unique_id = get_post_meta($post_id, 'mbt_unique_id', true);
	return empty($unique_id) ? '' : '<span class="mbt-meta-title">ISBN:</span> <span itemprop="isbn">'.$unique_id.'</span><br>';
}
function mbt_the_book_unique_id() {
	global $post;
	echo(mbt_get_book_unique_id($post->ID));
}



function mbt_get_book_series($post_id) {
	$series = NULL;

	$terms = get_the_terms($post_id, 'mbt_series');
	if(!empty($terms) and !is_wp_error($terms)) {
		foreach($terms as $term) {
			if(empty($series) or $series->term_id == $term->parent) { $series = $term; }
		}
	}

	return $series;
}
function mbt_get_book_series_list($post_id) {
	$output = '';
	$series = mbt_get_book_series($post_id);

	while(!empty($series) and !is_wp_error($series)) {
		$output = '<a itemprop="keywords" href="'.esc_url(get_term_link($series, 'mbt_series')).'">'.$series->name.'</a>'.(empty($output) ? '' : ', '.$output);
		$series = get_term_by('id', $series->parent, 'mbt_series');
	}

	if(!empty($output)) {
		$post = get_post($post_id);
		$series_order = get_post_meta($post->ID, 'mbt_series_order', true);
		$output = '<span class="mbt-meta-title">'.__('Series', 'mybooktable').':</span> '.$output.((!is_string($series_order) or strlen($series_order) < 1) ? '' : ', Book '.$series_order).'<br>';
	}

	return apply_filters('mbt_get_book_series_list', $output);
}
function mbt_get_the_term_list($post_id, $tax, $name, $name_plural, $type) {
	$terms = get_the_terms($post_id, $tax);
	if(is_wp_error($terms) or empty($terms)){ return ''; }

	if($type == 'author') {
		$sortfunc = function($a, $b) {
			$a = mbt_get_author_priority($a->term_id);
			$b = mbt_get_author_priority($b->term_id);
			return ($a > $b) ? -1 : (($a < $b) ? 1 : 0);
		};
		usort($terms, $sortfunc);
	}

	foreach($terms as $term) {
		$link = get_term_link($term, $tax);
		$term_links[] = '<a itemprop="'.$type.'" href="'.esc_url($link).'">'.$term->name.'</a>';
	}

	return '<span class="mbt-meta-title">'.(count($terms) > 1 ? $name_plural : $name).':</span> '.join(', ', $term_links).'<br>';
}
function mbt_the_book_series_list() {
	global $post;
	echo(mbt_get_book_series_list($post->ID));
}
function mbt_get_book_authors_list($post_id) {
	return apply_filters('mbt_get_book_authors_list', mbt_get_the_term_list($post_id, 'mbt_author', __('Author', 'mybooktable'), __('Authors', 'mybooktable'), 'author'));
}
function mbt_the_book_authors_list() {
	global $post;
	echo(mbt_get_book_authors_list($post->ID));
}
function mbt_get_book_genres_list($post_id) {
	return apply_filters('mbt_get_book_genres_list', mbt_get_the_term_list($post_id, 'mbt_genre', __('Genre', 'mybooktable'), __('Genres', 'mybooktable'), 'genre'));
}
function mbt_the_book_genres_list() {
	global $post;
	echo(mbt_get_book_genres_list($post->ID));
}
function mbt_get_book_tags_list($post_id) {
	return apply_filters('mbt_get_book_tags_list', mbt_get_the_term_list($post_id, 'mbt_tag', __('Tag', 'mybooktable'), __('Tags', 'mybooktable'), 'tag'));
}
function mbt_the_book_tags_list() {
	global $post;
	echo(mbt_get_book_tags_list($post->ID));
}



function mbt_the_domc_notice() {
	echo('<div class="mbt-affiliate-disclaimer">'.mbt_get_setting('domc_notice_text').'</div>');
}



function mbt_get_book_series_box($post_id) {
	$output = '';
	$series = mbt_get_book_series($post_id);
	if(!empty($series)) {
		$relatedbooks = new WP_Query(array('mbt_series' => $series->slug, 'order' => 'ASC', 'orderby' => 'meta_value_num', 'meta_key' => 'mbt_series_order', 'post__not_in' => array($post_id), 'posts_per_page' => -1));
		if(!empty($relatedbooks->posts)) {
			$title = apply_filters('mbt_book_series_box_title', sprintf(__('Other %s in', 'mybooktable'), mbt_get_product_name()).' "'.$series->name, $series->name);
			$output .= '<div style="clear:both"></div>';
			$output .= '<div class="mbt-book-series">';
			$output .= '<div class="mbt-book-series-title">'.$title.'":</div>';
			foreach($relatedbooks->posts as $relatedbook) {
				$size = 100;
				list($src, $width, $height) = mbt_get_book_image_src($relatedbook->ID);
				if(empty($width) or empty($height)) {
					list($src, $width, $height) = mbt_get_placeholder_image_src();
				};
				$scale = $size/max($width, $height);
				$width = floor($width*$scale);
				$width += $width%2;
				$height = floor($height*$scale);
				$height += $height%2;
				$lpadding = round(($size-$width)/2);
				$tpadding = round(($size-$height)/2);

				$output .= '<div class="mbt-book">';
				$output .= '<div class="mbt-book-images" style="-moz-box-sizing: border-box; box-sizing: border-box; width:'.$size.'px; height:'.$size.'px; padding: '.$tpadding.'px '.$lpadding.'px '.$tpadding.'px '.$lpadding.'px;"><a href="'.get_permalink($relatedbook->ID).'"><img width="'.$width.'" height="'.$height.'" src="'.$src.'" class="mbt-book-image"></a></div>';
				$output .= '<div class="mbt-book-title"><a href="'.get_permalink($relatedbook->ID).'">'.$relatedbook->post_title.'</a></div>';
				$output .= '<div style="clear:both"></div>';
				$output .= '</div>';
			}
			$output .= '<div style="clear:both"></div>';
			$output .= '</div>';
		}
	}
	return apply_filters('mbt_get_book_series_box', $output);
}
function mbt_the_book_series_box() {
	global $post;
	echo(mbt_get_book_series_box($post->ID));
}



function mbt_get_find_bookstore_box($post_id) {
	$output = '';
	$output .= '<div style="clear:both"></div>';
	$output .= '<div class="mbt-find-bookstore">';
	$output .= '<div class="mbt-find-bookstore-title">'.__('Find A Local Bookstore', 'mybooktable').'</div>';
	$output .= '<form class="mbt-find-bookstore-form" action="http://maps.google.com/maps">';
	$output .= '	<input type="text" class="mbt-city" placeholder="'.__('City', 'mybooktable').'" name="city" size="20">,';
	$output .= '	<input type="text" class="mbt-zip" placeholder="'.__('Zip', 'mybooktable').'" name="zip" size="5" maxlength="5">';
	$output .= '	<input type="submit" name="submit" value="'.__('Find Store', 'mybooktable').'">';
	$output .= '</form>';
	$output .= '<div style="clear:both"></div>';
	$output .= '</div>';
	return apply_filters('mbt_get_find_bookstore_box', $output);

}
function mbt_the_find_bookstore_box() {
	global $post;
	echo(mbt_get_find_bookstore_box($post->ID));
}



function mbt_get_reviews_box($post_id) {
	$output = '';
	$reviews_boxes = mbt_get_reviews_boxes();
	$current_reviews = mbt_get_setting('reviews_box');
	if(!empty($reviews_boxes[$current_reviews])) {
		$unique_id = get_post_meta($post_id, 'mbt_unique_id', true);
		$cache_id = 'mbt_'.$current_reviews.'_reviews_'.$post_id.'_'.$unique_id;
		if(false === ($output = get_transient($cache_id))) {
			$output = call_user_func_array($reviews_boxes[$current_reviews]['callback'], array());
			set_transient($cache_id, $output, HOUR_IN_SECONDS);
		}
	}
	return apply_filters('mbt_get_reviews_box', $output);

}
function mbt_the_reviews_box() {
	global $post;
	echo(mbt_get_reviews_box($post->ID));
}
