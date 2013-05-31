<?php

function mbt_templates_init() {
	//enqueue frontend styling
	add_action('wp_enqueue_scripts', 'mbt_enqueue_styles');
	add_action('wp_head', 'mbt_add_image_size_css');

	//override book page templates
	add_filter('template_include', 'mbt_load_book_templates');

	//modify the post query
	add_action('pre_get_posts', 'mbt_pre_get_posts');

	//register image size
	add_image_size('mbt_book_image', 400, 400, false);

	//modify body class
	add_filter('body_class', 'mbt_override_body_class', 100);

	//general hooks
	add_action('mbt_before_main_content', 'mbt_do_before_main_content');
	add_action('mbt_after_main_content', 'mbt_do_after_main_content');
	add_action('mbt_book_excerpt', 'mbt_do_book_excerpt');

	//booktable hooks
	add_action('mbt_before_booktable', 'mbt_do_before_booktable');
	add_action('mbt_after_booktable', 'mbt_do_after_booktable');

	//book archive hooks
	add_action('mbt_before_book_archive', 'mbt_do_before_book_archive');
	add_action('mbt_after_book_archive', 'mbt_do_after_book_archive');
	add_action('mbt_before_book_archive_listing', 'mbt_do_before_book_archive_listing');
	add_action('mbt_after_book_archive_listing', 'mbt_do_after_book_archive_listing');
	add_action('mbt_book_archive_header', 'mbt_do_book_archive_header');
	add_action('mbt_book_archive_no_results', 'mbt_do_book_archive_no_results');
	add_action('mbt_after_book_archive', 'mbt_do_book_archive_pagination');

	//single book hooks
	add_action('mbt_before_single_book', 'mbt_do_before_single_book');
	add_action('mbt_after_single_book', 'mbt_do_after_single_book');
	add_action('mbt_single_book_images', 'mbt_do_single_book_images');
	add_action('mbt_single_book_title', 'mbt_do_single_book_title');
	add_action('mbt_single_book_price', 'mbt_do_single_book_price');
	add_action('mbt_single_book_meta', 'mbt_do_single_book_meta');
	add_action('mbt_single_book_blurb', 'mbt_do_single_book_blurb');
	add_action('mbt_single_book_buybuttons', 'mbt_do_single_book_buybuttons');
	add_action('mbt_single_book_overview', 'mbt_do_single_book_overview');
	add_action('mbt_single_book_series', 'mbt_do_single_book_series');

	//book excerpt hooks
	add_action('mbt_before_book_excerpt', 'mbt_do_before_book_excerpt');
	add_action('mbt_after_book_excerpt', 'mbt_do_after_book_excerpt');
	add_action('mbt_book_excerpt_images', 'mbt_do_book_excerpt_images');
	add_action('mbt_book_excerpt_title', 'mbt_do_book_excerpt_title');
	add_action('mbt_book_excerpt_price', 'mbt_do_book_excerpt_price');
	add_action('mbt_book_excerpt_meta', 'mbt_do_book_excerpt_meta');
	add_action('mbt_book_excerpt_blurb', 'mbt_do_book_excerpt_blurb');
	add_action('mbt_book_excerpt_buybuttons', 'mbt_do_book_excerpt_buybuttons');
	if(mbt_get_setting('series_in_excerpts')) { add_action('mbt_book_excerpt_series', 'mbt_do_book_excerpt_series'); }

	//social media hooks
	if(mbt_get_setting('enable_socialmedia_badges_single_book')) { add_action('mbt_single_book_title', 'mbt_do_single_book_socialmedia_badges', 5); }
	if(mbt_get_setting('enable_socialmedia_badges_book_excerpt')) { add_action('mbt_book_excerpt_title', 'mbt_do_book_excerpt_socialmedia_badges', 5); }
	if(mbt_get_setting('enable_socialmedia_bar_single_book')) { add_action('mbt_single_book_overview', 'mbt_do_single_book_socialmedia_bar', 20); }

	add_action('template_redirect', 'mbt_add_twentyx_theme_support');

	do_action('mbt_templates_init');
}
add_action('mbt_init', 'mbt_templates_init');



/*---------------------------------------------------------*/
/* Template Overload Functions                             */
/*---------------------------------------------------------*/

function mbt_enqueue_styles() {
	wp_enqueue_style('mbt-style', apply_filters('mbt_css', plugins_url('css/frontend-style.css', dirname(__FILE__))));
	$style_pack_css = mbt_current_style_url('style.css');
	if($style_pack_css) { wp_enqueue_style('mbt-style-pack', apply_filters('mbt_style_pack_css', $style_pack_css)); }
}

function mbt_get_template_folders() {
	return apply_filters('mbt_template_folders', array());
}

function mbt_add_default_template_folder($folders) {
	$folders[] = plugin_dir_path(dirname(__FILE__)).'templates/';
	return $folders;
}
add_filter('mbt_template_folders', 'mbt_add_default_template_folder', 100);

function mbt_locate_template($name) {
	$locatedtemplate = locate_template('mybooktable/'.$name);
	if($locatedtemplate) { return $locatedtemplate; }

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

function mbt_load_book_templates($template) {
	if(is_post_type_archive('mbt_book') or is_tax('mbt_author') or is_tax('mbt_genre') or is_tax('mbt_series')) {
		$template = mbt_locate_template('archive-book.php');
	}

	if(is_singular('mbt_book')) {
		$template = mbt_locate_template('single-book.php');
	}

	if(mbt_is_booktable_page()) {
		$template = mbt_locate_template('page-booktable.php');
	}

	return $template;
}

function mbt_pre_get_posts($query) {
	if(!is_admin() and $query->is_page(mbt_get_setting('booktable_page')) and $query->is_main_query()) {
		global $mbt_is_booktable_page;
		$mbt_is_booktable_page = true;
	}

	if(!is_admin() and ($query->is_post_type_archive('mbt_book') or $query->is_tax('mbt_author') or $query->is_tax('mbt_series') or $query->is_tax('mbt_genre')) and $query->is_main_query()) {
		$posts_per_page = mbt_get_setting('posts_per_page');
		$query->set('posts_per_page', !empty($posts_per_page) ? $posts_per_page : get_option('posts_per_page'));
		$query->set('orderby', 'menu_order');
	}
}

function mbt_override_body_class($classes) {
	if(mbt_is_mbt_page()) {
		if(apply_filters('mbt_disable_singular', true)) {
			$key = array_search('singular', $classes);
			if($key !== false) { unset($classes[$key]); }
		}
		$classes[] = "mbt_page";
	}

	return $classes;
}

function mbt_add_image_size_css() {
	if(mbt_is_mbt_page()) {
		$image_size = mbt_get_setting('image_size');
		echo('<style type="text/css">');
		if($image_size == 'small') { echo('.mbt_book .mbt-book-images { width: 15%; } .mbt_book .mbt-book-right { width: 85%; } '); }
		else if($image_size == 'large') { echo('.mbt_book .mbt-book-images { width: 35%; } .mbt_book .mbt-book-right { width: 65%; } '); }
		else { echo('.mbt_book .mbt-book-images { width: 25%; } .mbt_book .mbt-book-right { width: 75%; } '); }
		echo('</style>');
	}
}

function mbt_add_twentyx_theme_support() {
	//remove custom excerpt mores
	if(mbt_is_mbt_page()) {
		remove_filter('excerpt_more', 'twentyten_auto_excerpt_more');
		remove_filter('get_the_excerpt', 'twentyten_custom_excerpt_more');
	}
}



/*---------------------------------------------------------*/
/* General Template Functions                              */
/*---------------------------------------------------------*/
function mbt_do_before_main_content() {
	mbt_include_template("before.php");
}
function mbt_do_after_main_content() {
	mbt_include_template("after.php");
}
function mbt_do_book_excerpt() {
	mbt_include_template("content-book.php");
}



/*---------------------------------------------------------*/
/* Booktable Template Functions                            */
/*---------------------------------------------------------*/
function mbt_do_before_booktable() {
	global $wp_query, $old_wp_query;
	$old_wp_query = $wp_query;
	$posts_per_page = mbt_get_setting('posts_per_page');
	$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'paged' => $old_wp_query->get('paged'), 'orderby' => 'menu_order', 'posts_per_page' => !empty($posts_per_page) ? $posts_per_page : get_option('posts_per_page')));
}
function mbt_do_after_booktable() {
	global $wp_query, $old_wp_query;
	$wp_query = $old_wp_query;
}



/*---------------------------------------------------------*/
/* Archive Template Functions                              */
/*---------------------------------------------------------*/

function mbt_do_before_book_archive() {
	mbt_include_template("archive-book/before.php");
}

function mbt_do_after_book_archive() {
	mbt_include_template("archive-book/after.php");
}

function mbt_do_before_book_archive_listing() {
	mbt_include_template("archive-book/before-listing.php");
}

function mbt_do_after_book_archive_listing() {
	mbt_include_template("archive-book/after-listing.php");
}

function mbt_do_book_archive_header() {
	mbt_include_template("archive-book/header.php");
}

function mbt_get_book_archive_image() {
	$query_obj = get_queried_object();
	if(empty($query_obj) or !property_exists($query_obj, 'taxonomy')) { return ''; }
	$img = mbt_get_taxonomy_image($query_obj->taxonomy, $query_obj->term_id);
	return apply_filters('mbt_get_book_archive_image', empty($img) ? '' : '<img class="mbt-archive-image" src="'.$img.'">');
}
function mbt_book_archive_image() {
	echo(mbt_get_book_archive_image());
}

function mbt_get_book_archive_title() {
	$output = '';
	if(mbt_is_booktable_page()) {
		$booktable_page = get_post(mbt_get_setting('booktable_page'));
		$output .= $booktable_page->post_title;
	} else if(is_post_type_archive('mbt_book')) {
		$output .= 'Books';
	} else if(is_tax('mbt_author')) {
		$output .= 'Author: '.get_queried_object()->name;
	} else if(is_tax('mbt_genre')) {
		$output .= 'Genre: '.get_queried_object()->name;
	} else if(is_tax('mbt_series')) {
		$output .= 'Series: '.get_queried_object()->name;
	}

	return apply_filters('mbt_get_book_archive_title', $output);
}
function mbt_book_archive_title() {
	echo(mbt_get_book_archive_title());
}

function mbt_get_book_archive_description() {
	$output = '';
	if(isset(get_queried_object()->description) and !empty(get_queried_object()->description)) {
		$output = '<div class="mbt-archive-description">'.get_queried_object()->description.'</div>';
	} else if(mbt_is_booktable_page()) {
		$booktable_page = get_post(mbt_get_setting('booktable_page'));
		$output = '<div class="mbt-archive-description">'.apply_filters('the_content', $booktable_page->post_content).'</div>';
	}

	return apply_filters('mbt_get_book_archive_description', $output);
}
function mbt_book_archive_description() {
	echo(mbt_get_book_archive_description());
}

function mbt_do_book_archive_no_results() {
	mbt_include_template("archive-book/no-results.php");
}

function mbt_do_book_archive_pagination() {
	mbt_include_template("archive-book/pagination.php");
}



/*---------------------------------------------------------*/
/* Single Book Template Functions                          */
/*---------------------------------------------------------*/
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
function mbt_do_single_book_series() {
	mbt_include_template("single-book/series.php");
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
	mbt_include_template("content-book/before.php");
}
function mbt_do_after_book_excerpt() {
	mbt_include_template("content-book/after.php");
}
function mbt_do_book_excerpt_images() {
	mbt_include_template("content-book/images.php");
}
function mbt_do_book_excerpt_title() {
	mbt_include_template("content-book/title.php");
}
function mbt_do_book_excerpt_price() {
	mbt_include_template("content-book/price.php");
}
function mbt_do_book_excerpt_meta() {
	mbt_include_template("content-book/meta.php");
}
function mbt_do_book_excerpt_blurb() {
	mbt_include_template("content-book/blurb.php");
}
function mbt_do_book_excerpt_buybuttons() {
	mbt_include_template("content-book/buybuttons.php");
}
function mbt_do_book_excerpt_series() {
	mbt_include_template("content-book/series.php");
}
function mbt_do_book_excerpt_socialmedia_badges() {
	mbt_include_template("content-book/socialmedia-badges.php");
}



/*---------------------------------------------------------*/
/* General Book Template Functions                         */
/*---------------------------------------------------------*/

function mbt_get_book_image($post_id) {
	$src = '';

	$image = apply_filters('mbt_book_image', wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'mbt_book_image'));
	if($image) {
		list($src, $width, $height) = $image;
	} else {
		$src = apply_filters('mbt_book_placeholder', plugins_url('images/book-placeholder.jpg', dirname(__FILE__)));
	}

	return apply_filters('mbt_get_book_image', '<img src="'.$src.'" alt="'.get_the_title($post_id).'" class="mbt-book-image">');
}
function mbt_the_book_image() {
	global $post;
	echo(mbt_get_book_image($post->ID));
}



function mbt_get_book_price($post_id) {
	$price = get_post_meta($post_id, 'mbt_price', true);
	return apply_filters('mbt_get_book_price', empty($price) ? '' : "$".number_format((double)(preg_replace("/[^0-9,.]/", "", $price)), 2), $post_id);
}
function mbt_the_book_price() {
	global $post;
	echo(mbt_get_book_price($post->ID));
}

function mbt_add_book_sale_price($price, $post_id) {
	$sale_price = get_post_meta($post_id, 'mbt_sale_price', true);
	if(!empty($sale_price)) {
		$price = '<span class="normal-price">'.$price.'</span><span class="sale-price">$'.number_format((double)(preg_replace("/[^0-9,.]/", "", $sale_price)), 2).'</span>';
	}
	return $price;
}
add_filter('mbt_get_book_price', 'mbt_add_book_sale_price', 20, 2);



function mbt_get_book_sample_url($post_id) {
	return apply_filters('mbt_get_book_sample_url', get_post_meta($post_id, "mbt_sample_url", true));
}
function mbt_the_book_sample_url() {
	global $post;
	echo(mbt_get_book_sample_url($post->ID));
}

function mbt_get_book_sample($post_id) {
	$url = mbt_get_book_sample_url($post_id);
	return empty($url) ? '' : apply_filters('mbt_get_book_sample', '<br><a class="mbt-book-sample" href="'.$url.'">Download Sample Chapter</a>');
}
function mbt_the_book_sample() {
	global $post;
	echo(mbt_get_book_sample($post->ID));
}



function mbt_get_book_socialmedia_badges($post_id) {
	$url = urlencode(get_permalink($post_id));
	$output = '';

	$output .= '<iframe src="https://plusone.google.com/_/+1/fastbutton?url='.$url.'&size=tall&count=true&annotation=bubble" class="gplusone" style="width: 50px; height: 61px; margin: 0px; border: none; overflow: hidden;" frameborder="0" scrolling="no" allowtransparency="true"></iframe>';
	$output .= '<iframe src="http://www.facebook.com/plugins/like.php?href='.$url.'&layout=box_count" class="fblike" style="width: 50px; height: 61px; margin: 0px; border: none; overflow: hidden;" scrolling="no" frameborder="0" allowtransparency="true"></iframe>';

	return apply_filters('mbt_get_book_socialmedia_badges', $output);
}
function mbt_the_book_socialmedia_badges() {
	global $post;
	echo(mbt_get_book_socialmedia_badges($post->ID));
}

function mbt_get_book_socialmedia_bar($post_id) {
	$url = urlencode(get_permalink($post_id));
	$output = '';

	if(function_exists('install_ShareThis')) {
		$output .= st_add_widget('');
	} else {
		$output .= '<iframe src="https://plusone.google.com/_/+1/fastbutton?url='.$url.'&size=medium&count=true&annotation=bubble" class="gplusone" style="width: 75px; height: 20px; margin: 0px; border: none; overflow: hidden;" frameborder="0" scrolling="no" allowtransparency="true"></iframe>';
		$output .= '<iframe src="http://www.facebook.com/plugins/like.php?href='.$url.'&layout=button_count" class="fblike" style="width: 75px; height: 20px; margin: 0px; border: none; overflow: hidden;" scrolling="no" frameborder="0" allowtransparency="true"></iframe>';
		$output .= '<iframe src="http://platform.twitter.com/widgets/tweet_button.html?url='.$url.'&count=horizontal&size=m" class="twittershare" style="height: 20px; width: 100px; margin: 0px; border: none; overflow: hidden;" allowtransparency="true" frameborder="0" scrolling="no"></iframe>';
	}

	return apply_filters('mbt_get_book_socialmedia_bar', $output);
}
function mbt_the_book_socialmedia_bar() {
	global $post;
	echo(mbt_get_book_socialmedia_bar($post->ID));
}



function mbt_format_buybuttons($book_buybuttons) {
	$output = '';

	$buybuttons = mbt_get_buybuttons();
	if(!empty($book_buybuttons)) {
		foreach($book_buybuttons as $button) {
			if(empty($buybuttons[$button['type']])) { continue; }
			$output .= mbt_buybutton_button($button, $buybuttons[$button['type']]);
		}
	}

	return apply_filters('mbt_format_buybuttons', $output);
}
function mbt_the_book_buybuttons() {
	global $post;
	$book_buybuttons = mbt_get_book_buybuttons($post->ID, array('display' => array('featured', 'book_only')));
	echo(mbt_format_buybuttons($book_buybuttons));
}
function mbt_the_book_buybuttons_featured() {
	global $post;
	$book_buybuttons = mbt_get_book_buybuttons($post->ID, array('display' => 'featured'));
	echo(mbt_format_buybuttons($book_buybuttons));
}
function mbt_the_book_buybuttons_textonly() {
	global $post;
	$book_buybuttons = mbt_get_book_buybuttons($post->ID, array('display' => 'text_only'));

	if(!empty($book_buybuttons)) {
		echo('<div class="mbt-book-buybuttons-textonly">');
		echo('<h3>Other book sellers:</h3>');
		echo('<ul>'.mbt_format_buybuttons($book_buybuttons).'</ul>');
		echo('</div>');
	}
}



function mbt_get_book_blurb($post_id, $read_more = false) {
	$post = get_post($post_id);
	$output = apply_filters('get_the_excerpt', $post->post_excerpt);
	if($read_more) { $output .= apply_filters('mbt_read_more', ' <a href="'.get_permalink($post_id).'" class="read-more">'.apply_filters('mbt_read_more_text', 'More info →').'</a>'); }
	return apply_filters('mbt_get_book_blurb', $output);
}
function mbt_the_book_blurb($read_more = false) {
	global $post;
	echo(mbt_get_book_blurb($post->ID, $read_more));
}



function mbt_get_book_unique_id($post_id) {
	$unique_id = get_post_meta($post_id, 'mbt_unique_id', true);
	return empty($unique_id) ? "" : "<span class='meta-title'>ISBN:</span> ".$unique_id."<br>";
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
		$output = '<a href="'.esc_url(get_term_link($series, 'mbt_series')).'">'.$series->name.'</a>'.(empty($output) ? '' : ', '.$output);
		$series = get_term_by('id', $series->parent, 'mbt_series');
	}

	if(!empty($output)) {
		$post = get_post($post_id);
		$series_order = get_post_meta($post->ID, 'mbt_series_order', true);
		$output = '<span class="meta-title">Series:</span> '.$output.(empty($series_order) ? '' : ', Book '.$series_order).'<br>';
	}

	return apply_filters('mbt_get_book_series_list', $output);
}
function mbt_get_the_term_list($post_id, $tax, $name, $name_plural) {
	$terms = get_the_terms($post_id, $tax);
	if(is_wp_error($terms) or empty($terms)){ return ''; }

	foreach($terms as $term) {
		$link = get_term_link($term, $tax);
		$term_links[] = '<a href="'.esc_url($link).'">'.$term->name.'</a>';
	}

	return '<span class="meta-title">'.(count($terms) > 1 ? $name_plural : $name).':</span> '.join(', ', $term_links).'<br>';
}
function mbt_the_book_series_list() {
	global $post;
	echo(mbt_get_book_series_list($post->ID));
}
function mbt_get_book_authors_list($post_id) {
	return apply_filters('mbt_get_book_authors_list', mbt_get_the_term_list($post_id, 'mbt_author', 'Author', 'Authors', 'author'));
}
function mbt_the_book_authors_list() {
	global $post;
	echo(mbt_get_book_authors_list($post->ID));
}
function mbt_get_book_genres_list($post_id) {
	return apply_filters('mbt_get_book_genres_list', mbt_get_the_term_list($post_id, 'mbt_genre', 'Genre', 'Genres', 'tag'));
}
function mbt_the_book_genres_list() {
	global $post;
	echo(mbt_get_book_genres_list($post->ID));
}



function mbt_get_book_series_box($post_id) {
	$output = '';
	$series = mbt_get_book_series($post_id);
	if(!empty($series)) {
		$relatedbooks = new WP_Query(array('mbt_series' => $series->slug, 'order' => 'ASC', 'orderby' => 'meta_value', 'meta_key' => 'mbt_series_order', 'post__not_in' => array($post_id)));
		if(!empty($relatedbooks->posts)) {
			$output .= '<div class="mbt-book-series">';
			$output .= '<div class="mbt-book-series-title">Other books in "'.$series->name.'":</div>';
			foreach($relatedbooks->posts as $relatedbook) {
				$output .= '<div class="mbt-book">';
				$output .= '<div class="mbt-book-images"><a href="'.get_permalink($relatedbook->ID).'">'.mbt_get_book_image($relatedbook->ID).'</a></div>';
				$output .= '<div class="mbt-book-title"><a href="'.get_permalink($relatedbook->ID).'">'.$relatedbook->post_title.'</a></div>';
				$output .= '<div class="clear:both"></div>';
				$output .= '</div>';
			}
			$output .= '</div>';
		}
	}
	return apply_filters('mbt_get_book_series_box', $output);
}
function mbt_the_book_series_box() {
	global $post;
	echo(mbt_get_book_series_box($post->ID));
}
