<?php

function mbt_totallybooked_init() {
	add_filter('mbt_importers', 'mbt_add_totallybooked_importer');
}
add_action('mbt_init', 'mbt_totallybooked_init');

function mbt_add_totallybooked_importer($importers) {
	$exists = class_exists('TotallyBooked');

	$importers['totallybooked'] = array(
		'name' => __('Totally Booked', 'mybooktable'),
		'desc' => __('Import your books from the Totally Booked plugin.', 'mybooktable'),
		'page_title' => 'Totally Booked Import',
		'get_book_list' => 'mbt_totallybooked_get_books',
		'disabled' => ($exists ? '' : 'Totally Booked plugin not detected'),
	);
	return $importers;
}

function mbt_totallybooked_get_books() {
	$books = array();
	$query = new WP_Query(array('post_type' => 'tb_book', 'posts_per_page' => -1));
	foreach($query->posts as $book) {
		$urls = array(
			'amazon_url' => 'amazon',
			'audible_url' => 'audible',
			'barnes_noble_url' => 'bnn',
			'books_a_million_url' => 'bam',
			'christian_books_url' => 'cbd',
			'googleplay_url' => 'googleplay',
			'indiebound_url' => 'indiebound',
			'itunes_url' => 'ibooks',
			'kobo_url' => 'kobo',
			'smashwords_url' => 'smashwords',
			'sony_url' => 'sony',
		);

		$new_book = array();
		$new_book['source_id'] = $book->ID;
		$new_book['title'] = $book->post_title;
		$new_book['content'] = $book->post_content;
		$new_book['excerpt'] = $book->post_excerpt;
		$new_book['authors'] = mbt_get_totallybooked_taxonomy($book->ID, 'tb_author', 'mbt_author');
		$new_book['series'] = mbt_get_totallybooked_taxonomy($book->ID, 'tb_series', 'mbt_series');
		$new_book['genres'] = mbt_get_totallybooked_taxonomy($book->ID, 'tb_genre', 'mbt_genre');
		$new_book['unique_id'] = get_post_meta($book->ID, 'isbn_number', true);
		$new_book['buybuttons'] = array();
		foreach ($urls as $name => $store) {
			$link = get_post_meta($book->ID, $name, true);
			if(!empty($link)) { $new_book['buybuttons'][] = array('display' => 'button', 'store' => $store, 'url' => $link); }
		}
		$new_book['image_id'] = get_post_meta($book->ID, '_thumbnail_id', true);
		$new_book['imported_book_id'] = get_post_meta($book->ID, 'mbt_imported_book_id', true);

		$books[] = $new_book;
	}

	return $books;
}

function mbt_get_totallybooked_taxonomy($post_id, $taxonomy) {
	$returns = array();
	$terms = wp_get_object_terms($post_id, $taxonomy);
	foreach($terms as $term) {
		$returns[] = $term->name;
	}
	return $returns;
}
