<?php

function mbt_integrate_woo_breadcrumbs($trail) {
	if(is_post_type_archive('mbt_books') or is_tax('mbt_authors') or is_tax('mbt_genres') or is_tax('mbt_series') or is_singular('mbt_books')) {
		$page_id = mbt_get_setting('booktable_page');
		$page_link = '<a href="'.get_permalink($page_id).'">'.get_the_title($page_id).'</a>';
		array_splice($trail, 1, count($trail) - 1, array($page_link, $trail['trail_end']));
	}
	return $trail;
}
add_filter('woo_breadcrumbs_trail', 'mbt_integrate_woo_breadcrumbs');