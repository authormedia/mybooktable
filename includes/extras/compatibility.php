<?php

/*---------------------------------------------------------*/
/* Compatibility Mode Functions                            */
/*---------------------------------------------------------*/

function mbt_compat_init() {
	if(mbt_get_setting('compatibility_mode')) {
		//override page content
		add_filter('the_content', 'mbt_compat_custom_page_content', 100, 2);

		//modify the post query
		add_action('pre_get_posts', 'mbt_compat_pre_get_posts', 30);
		add_action('wp', 'mbt_compat_override_query_posts', -999);

		//override page template
		remove_filter('template_include', 'mbt_load_book_templates');
		add_filter('template_include', 'mbt_compat_load_book_templates');
	}
}
add_action('mbt_init', 'mbt_compat_init', 11);



/*---------------------------------------------------------*/
/* Template Overload Functions                             */
/*---------------------------------------------------------*/

function mbt_compat_custom_page_content($content) {
	global $mbt_in_custom_page_content, $wp_query;

	if(!empty($mbt_in_custom_page_content)) { return $content; }

	$template = '';

	if((mbt_is_booktable_page() and $wp_query->post->ID == mbt_get_setting('booktable_page')) or (mbt_is_taxonomy_query() and $wp_query->post->ID == 0)) {
		$template = mbt_locate_template('archive-book/content.php');
		remove_action('mbt_book_archive_header_title', 'mbt_do_book_archive_header_title');
	} else if(is_singular('mbt_book')) {
		$template = mbt_locate_template('single-book/content.php');
		remove_action('mbt_single_book_title', 'mbt_do_single_book_title');
	}

	if($template) {
		$mbt_in_custom_page_content = true;
		ob_start();

		include($template);

		$content = ob_get_contents();
		ob_end_clean();
		$mbt_in_custom_page_content = false;
	}

	return $content;
}

function mbt_compat_load_book_templates($template) {
	if(is_singular('mbt_book') or mbt_is_taxonomy_query()) {
		$template = locate_template('page.php');
		if(empty($template)) { $template = locate_template('index.php'); }
	}
	return $template;
}

function mbt_compat_pre_get_posts($query) {
	if(!is_admin() and $query->is_main_query() and ($query->is_post_type_archive('mbt_book') or $query->is_tax('mbt_author') or $query->is_tax('mbt_series') or $query->is_tax('mbt_genre') or $query->is_tax('mbt_tag'))) {
		global $mbt_is_taxonomy_query, $mbt_taxonomy_query;
		$mbt_is_taxonomy_query = true;
		if($query->get('mbt_author')) {
			$mbt_taxonomy_query = new WP_Query(array('post_type' => 'mbt_book', 'mbt_author' => $query->get('mbt_author'), 'paged' => $query->get('paged'), 'orderby' => 'menu_order', 'posts_per_page' => mbt_get_posts_per_page()));
		} else if($query->get('mbt_series')) {
			$mbt_taxonomy_query = new WP_Query(array('post_type' => 'mbt_book', 'mbt_series' => $query->get('mbt_series'), 'paged' => $query->get('paged'), 'orderby' => 'menu_order', 'posts_per_page' => mbt_get_posts_per_page()));
		} else if($query->get('mbt_genre')) {
			$mbt_taxonomy_query = new WP_Query(array('post_type' => 'mbt_book', 'mbt_genre' => $query->get('mbt_genre'), 'paged' => $query->get('paged'), 'orderby' => 'menu_order', 'posts_per_page' => mbt_get_posts_per_page()));
		} else if($query->get('mbt_tag')) {
			$mbt_taxonomy_query = new WP_Query(array('post_type' => 'mbt_book', 'mbt_tag' => $query->get('mbt_tag'), 'paged' => $query->get('paged'), 'orderby' => 'menu_order', 'posts_per_page' => mbt_get_posts_per_page()));
		} else {
			$mbt_taxonomy_query = new WP_Query(array('post_type' => 'mbt_book', 'paged' => $query->get('paged'), 'orderby' => 'menu_order', 'posts_per_page' => mbt_get_posts_per_page()));
		}
		add_action('mbt_before_book_archive', 'mbt_do_before_taxonomy_query', 0);
		add_action('mbt_after_book_archive', 'mbt_do_after_taxonomy_query', 20);
	}
}

function mbt_compat_override_query_posts() {
	if(mbt_is_taxonomy_query()) {
		$post = new WP_Post((object)array(
			"ID" => 0,
			"post_author" => "1",
			"post_date" => "",
			"post_date_gmt" => "",
			"post_modified" => "",
			"post_modified_gmt" => "",
			"post_content" => "",
			"post_content_filtered" => "",
			"post_excerpt" => "",
			"post_title" => mbt_get_book_archive_title(),
			"post_status" => "publish",
			"comment_status" => "closed",
			"ping_status" => "closed",
			"post_password" => "",
			"post_name" => "",
			"to_ping" => "",
			"pinged" => "",
			"post_parent" => 0,
			"guid" => "",
			"menu_order" => 0,
			"post_type" => "page",
			"post_mime_type" => "",
			"comment_count" => "0",
			"filter" => "raw",
		));

		global $wp_query;
		$wp_query->post = $post;
		$wp_query->posts = array($post);
		$wp_query->post_count = 1;
		$wp_query->is_page = true;
		$wp_query->is_singular = true;
		$wp_query->is_tax = false;
		$wp_query->is_archive = false;
		$wp_query->is_post_type_archive = false;
		$wp_query->queried_object = $post;
		$wp_query->queried_object_id = 0;
		$GLOBALS['post'] = $wp_query->post;
		$GLOBALS['posts'] = &$wp_query->posts;
	}
}

function mbt_is_taxonomy_query() {
	global $mbt_is_taxonomy_query;
	return !empty($mbt_is_taxonomy_query);
}

function mbt_do_before_taxonomy_query() {
	global $wp_query, $posts, $post, $id, $mbt_old_wp_query, $mbt_old_posts, $mbt_old_post, $mbt_old_id, $mbt_taxonomy_query;
	if($wp_query->is_main_query()) {
		$mbt_old_wp_query = $wp_query;
		$mbt_old_posts = $posts;
		$mbt_old_post = $post;
		$mbt_old_id = $id;
		$wp_query = $mbt_taxonomy_query;
	}
}

function mbt_do_after_taxonomy_query() {
	global $wp_query, $posts, $post, $id, $mbt_old_wp_query, $mbt_old_posts, $mbt_old_post, $mbt_old_id;
	if($mbt_old_wp_query) {
		$wp_query = $mbt_old_wp_query;
		$posts = $mbt_old_posts;
		$post = $mbt_old_post;
		$id = $mbt_old_id;
	}
}
