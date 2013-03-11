<?php

function mbt_templates_init() {
	//enqueue frontend styling
	add_action('wp_enqueue_scripts', 'mbt_enqueue_styles');

	//override book page templates
	add_filter('template_include', 'mbt_load_book_templates');

	//modify the post query
	add_action('pre_get_posts', 'mbt_pre_get_posts');

	//register booktable shortcode
	add_shortcode('mbt_booktable', 'mbt_booktable_shortcode');

	//modify body class
	add_filter('body_class', 'mbt_override_body_class', 100);

	//general hooks
	add_action('mbt_before_main_content', 'mbt_do_before_main_content');
	add_action('mbt_after_main_content', 'mbt_do_after_main_content');
	add_action('mbt_book_excerpt', 'mbt_do_book_excerpt');

	//book archive hooks
	add_action('mbt_before_book_archive', 'mbt_do_before_book_archive');
	add_action('mbt_after_book_archive', 'mbt_do_after_book_archive');
	add_action('mbt_before_book_archive_listing', 'mbt_do_before_book_archive_listing');
	add_action('mbt_after_book_archive_listing', 'mbt_do_after_book_archive_listing');
	add_action('mbt_book_archive_header', 'mbt_do_book_archive_header');
	add_action('mbt_book_archive_no_results', 'mbt_do_book_archive_no_results');

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
	if(mbt_is_socialmedia_active()) { add_action('mbt_single_book_socialmedia', 'mbt_do_single_book_socialmedia'); }

	//book excerpt hooks
	add_action('mbt_before_book_excerpt', 'mbt_do_before_book_excerpt');
	add_action('mbt_after_book_excerpt', 'mbt_do_after_book_excerpt');
	add_action('mbt_book_excerpt_images', 'mbt_do_book_excerpt_images');
	add_action('mbt_book_excerpt_title', 'mbt_do_book_excerpt_title');
	add_action('mbt_book_excerpt_price', 'mbt_do_book_excerpt_price');
	add_action('mbt_book_excerpt_meta', 'mbt_do_book_excerpt_meta');
	add_action('mbt_book_excerpt_blurb', 'mbt_do_book_excerpt_blurb');
	add_action('mbt_book_excerpt_buybuttons', 'mbt_do_book_excerpt_buybuttons');
	if(mbt_get_setting('socialmedia_in_excerpts') and mbt_is_socialmedia_active()) { add_action('mbt_book_excerpt_socialmedia', 'mbt_do_book_excerpt_socialmedia'); }
	if(mbt_get_setting('series_in_excerpts')) { add_action('mbt_book_excerpt_series', 'mbt_do_book_excerpt_series'); }

	add_action('template_redirect', 'mbt_add_twentyx_theme_support');

	do_action('mbt_templates_init');
}
add_action('mbt_init', 'mbt_templates_init');

/*---------------------------------------------------------*/
/* Template Overload Functions                             */
/*---------------------------------------------------------*/

function mbt_enqueue_styles() {
	wp_enqueue_style('book-table-style', apply_filters('mbt_frontend_styles', plugins_url('css/frontend-style.css', dirname(__FILE__))));
}

function mbt_locate_template($name) {
	$locatedtemplate = locate_template('mybooktable/'.$name);

	//allow template plugins to override where we look for templates
	$template_dirs = apply_filters('mbt_template_dirs', array(plugin_dir_path(dirname(__FILE__)).'templates/'));

	//look backward through template list so that the last appended folder will be searched first
	for($dir = count($template_dirs)-1; empty($locatedtemplate) and $dir >= 0; $dir--) {
		$locatedtemplate = $template_dirs[$dir].$name;
		if(!file_exists($locatedtemplate)) { $locatedtemplate = ''; }
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

	return $template;
}

//modify post query
function mbt_pre_get_posts($query) {
	if(!is_admin() and get_post_type() != 'mbt_book' and $query->is_main_query()) {
		$query->query_vars['posts_per_page'] = !empty($mbt_get_settings['posts_per_page']) ? mbt_get_setting('posts_per_page') : get_option('posts_per_page');
	}
}

//function for booktable page
function mbt_booktable_shortcode($atts) {
	global $wp_query, $post;
	$old_wp_query = $wp_query;
	$wp_query = new WP_Query(array('post_type' => 'mbt_book'));

	if(have_posts()) {
		?> <div class="mbt-book-listing"> <?php
		do_action('mbt_before_bookstore_listing');
		while(have_posts()) {
			the_post();
			do_action('mbt_book_excerpt');
		}
		do_action('mbt_after_bookstore_listing');
		?> </div> <?php
	} else {
		do_action('mbt_book_archive_no_results');
	}

	$wp_query = $old_wp_query;
}

function mbt_override_body_class($classes) {
	if(is_singular('mbt_book')) {
		if(apply_filters('mbt_disable_singular', true)) {
			$key = array_search('singular', $classes);
			if($key !== false ) { unset($classes[$key]); }
		}
		$classes[] = "mbt_page";
	}

	return $classes;
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
	if(empty($query_obj) or !property_exists($query_obj, 'taxonomy')) { return; }
	$img = mbt_get_taxonomy_image($query_obj->taxonomy, $query_obj->term_id);
	return apply_filters('mbt_get_book_archive_image', empty($img) ? '' : '<img class="mbt-archive-image" src="'.$img.'">');
}
function mbt_book_archive_image() {
	echo(mbt_get_book_archive_image());
}

function mbt_get_book_archive_title() {
	$output = '';
	if(is_post_type_archive('mbt_book')) {
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
	}

	return apply_filters('mbt_get_book_archive_description', $output);
}
function mbt_book_archive_description() {
	echo(mbt_get_book_archive_description());
}

function mbt_do_book_archive_no_results() {
	mbt_include_template("archive-book/no-results.php");
}

/*---------------------------------------------------------*/
/* Book Excerpt Template Functions                         */
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
function mbt_do_single_book_socialmedia() {
	mbt_include_template("single-book/socialmedia.php");
}



/*---------------------------------------------------------*/
/* Single Book Template Functions                          */
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
function mbt_do_book_excerpt_socialmedia() {
	mbt_include_template("content-book/socialmedia.php");
}
function mbt_do_book_excerpt_series() {
	mbt_include_template("content-book/series.php");
}



/*---------------------------------------------------------*/
/* General Book Template Functions                         */
/*---------------------------------------------------------*/

function mbt_get_book_image($post_id, $size = 0) {
	$src = '';

	$image = apply_filters('mbt_book_image', wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'book-image'));
	if($image) {
		list($src, $width, $height) = $image;
	} else {
		$src = plugins_url('images/book-placeholder.png', dirname(__FILE__));
	}

	return apply_filters('mbt_get_book_image', '<img itemprop="image" src="'.$src.'" class="mbt-book-image">');
}
function mbt_the_book_image($size = 0) {
	global $post;
	echo(mbt_get_book_image($post->ID, $size));
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



function mbt_get_book_socialmedia($post_id) {
	$url = urlencode(get_permalink($post_id));
	$output = '';

	if(function_exists('install_ShareThis')) {
		$output .= st_add_widget('');
	} else {
		$output .= '<iframe src="https://plusone.google.com/_/+1/fastbutton?url='.$url.'&size=medium&count=true&annotation=bubble" class="gplusone" style="width: 75px; height: 20px; margin: 0px; border: none; overflow: hidden;" frameborder="0" scrolling="no" allowtransparency="true"></iframe>';
		$output .= '<iframe src="http://www.facebook.com/plugins/like.php?href='.$url.'&layout=button_count" class="fblike" style="width: 75px; height: 20px; margin: 0px; border: none; overflow: hidden;" scrolling="no" frameborder="0" allowtransparency="true"></iframe>';
		$output .= '<iframe src="http://platform.twitter.com/widgets/tweet_button.html?url='.$url.'&count=horizontal&size=m" class="twittershare" style="height: 20px; width: 100px; margin: 0px; border: none; overflow: hidden;" allowtransparency="true" frameborder="0" scrolling="no"></iframe>';
	}

	return apply_filters('mbt_get_book_socialmedia', $output);
}
function mbt_the_book_socialmedia() {
	global $post;
	echo(mbt_get_book_socialmedia($post->ID));
}



function mbt_get_book_buybuttons($post_id) {
	$buybuttons = get_post_meta($post_id, "mbt_buybuttons", true);
	return apply_filters('mbt_get_book_buybuttons', empty($buybuttons) ? array() : $buybuttons);
}
function mbt_get_book_featured_buybuttons($post_id) {
	$buybuttons = mbt_get_book_buybuttons($post_id);
	$newbuybuttons = array();

	$featured_buybuttons = mbt_get_setting('featured_buybuttons');
	for($i = 0; $i < count($buybuttons); $i++)
	{
		if(isset($featured_buybuttons[$buybuttons[$i]['type']])) { $newbuybuttons[] = $buybuttons[$i]; }
	}

	return apply_filters('mbt_get_book_featured_buybuttons', $newbuybuttons);
}
function mbt_get_formatted_book_buybuttons($post_id, $featured_only = false) {
	$output = '';

	$buybuttons = mbt_get_buybuttons();
	$book_buybuttons = $featured_only ? mbt_get_book_featured_buybuttons($post_id) : mbt_get_book_buybuttons($post_id);
	if(!empty($book_buybuttons)) {
		for($i = 0; $i < count($book_buybuttons); $i++)
		{
			$output .= $buybuttons[$book_buybuttons[$i]['type']]['button']($book_buybuttons[$i]);
		}
	}

	return apply_filters('mbt_get_formatted_book_buybuttons', $output);
}
function mbt_the_book_buybuttons($featured_only = false) {
	global $post;
	echo(mbt_get_formatted_book_buybuttons($post->ID, $featured_only));
}



function mbt_get_book_blurb($post_id, $read_more = false) {
	$output = get_the_excerpt();
	if($read_more) { $output .= apply_filters('mbt_read_more', ' <a href="'.get_permalink($post_id).'">'.apply_filters('mbt_read_more_text', 'More info →').'</a>'); }
	return apply_filters('mbt_get_book_blurb', $output);
}
function mbt_the_book_blurb($read_more = false) {
	global $post;
	echo(mbt_get_book_blurb($post->ID, $read_more));
}



function mbt_get_book_authors_list($post_id, $before = "<span class='meta-title'>Authors:</span> ", $sep = ", ", $after = "<br>") {
	return apply_filters('mbt_get_book_authors_list', get_the_term_list($post_id, 'mbt_author', $before, $sep, $after));
}
function mbt_the_book_authors_list($before = "<span class='meta-title'>Authors:</span> ", $sep = ", ", $after = "<br>") {
	global $post;
	echo(mbt_get_book_authors_list($post->ID, $before, $sep, $after));
}
function mbt_get_book_series_list($post_id, $before = "<span class='meta-title'>Series:</span> ", $sep = ", ", $after = "<br>") {
	return apply_filters('mbt_get_book_series_list', get_the_term_list($post_id, 'mbt_series', $before, $sep, $after));
}
function mbt_the_book_series_list($before = "<span class='meta-title'>Series:</span> ", $sep = ", ", $after = "<br>") {
	global $post;
	echo(mbt_get_book_series_list($post->ID, $before, $sep, $after));
}
function mbt_get_book_genres_list($post_id, $before = "<span class='meta-title'>Genres:</span> ", $sep = ", ", $after = "<br>") {
	return apply_filters('mbt_get_book_genres_list', get_the_term_list($post_id, 'mbt_genre', $before, $sep, $after));
}
function mbt_the_book_genres_list($before = "<span class='meta-title'>Genres:</span> ", $sep = ", ", $after = "<br>") {
	global $post;
	echo(mbt_get_book_genres_list($post->ID, $before, $sep, $after));
}



function mbt_get_book_series($post_id) {
	$output = '';
	$series_all = wp_get_post_terms($post_id, 'mbt_series');
	if(!empty($series_all)) {
		foreach($series_all as $series) {
			$relatedbooks = new WP_Query(array('mbt_series' => $series->slug, 'post__not_in' => array($post_id)));
			if(!empty($relatedbooks->posts)) {
				$output .= '<div class="mbt-book-series">';
				$output .= '<div class="mbt-book-series-title">Other books in "'.$series->name.'":</div>';
				foreach($relatedbooks->posts as $relatedbook) {
					$output .= '<div class="mbt-book">';
					$output .= '<div class="mbt-book-images"><a href="'.get_permalink($relatedbook->ID).'">'.mbt_get_book_image($relatedbook->ID, 150).'</a></div>';
					$output .= '<div class="mbt-book-title"><a href="'.get_permalink($relatedbook->ID).'">'.$relatedbook->post_title.'</a></div>';
					$output .= '<div class="clear:both"></div>';
					$output .= '</div>';
				}
				$output .= '</div>';
			}
		}
	}
	return apply_filters('mbt_get_book_series', $output);
}
function mbt_the_book_series() {
	global $post;
	echo(mbt_get_book_series($post->ID));
}
