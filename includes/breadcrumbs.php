<?php

function mbt_integrate_woo_breadcrumbs($trail) {
	if(is_post_type_archive('mbt_book') or is_tax('mbt_author') or is_tax('mbt_genre') or is_tax('mbt_series') or is_singular('mbt_book')) {
		$page_id = mbt_get_setting('booktable_page');
		if($page_id) {
			$page_link = '<a href="'.get_permalink($page_id).'">'.get_the_title($page_id).'</a>';
			array_splice($trail, 1, count($trail) - 1, array($page_link, $trail['trail_end']));
		} elseif(get_queried_object()->name != "mbt_book") {
			$booksarchive = get_post_type_object('mbt_book');
			$slug = get_site_url() . '/' . $booksarchive->rewrite['slug'];
			$name = $booksarchive->labels->name;
			$page_link = '<a href="'.$slug.'">'.$name.'</a>';
			array_splice($trail, 1, count($trail) - 1, array($page_link, $trail['trail_end']));
		}
	}
	return $trail;
}
add_filter('woo_breadcrumbs_trail', 'mbt_integrate_woo_breadcrumbs');