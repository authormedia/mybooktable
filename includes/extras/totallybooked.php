<?php

function mbt_totallybooked_init() {
	add_action('plugins_loaded', 'mbt_totallybooked_compat', 20);
}
add_action('mbt_init', 'mbt_totallybooked_init');

function mbt_totallybooked_compat() {
	if(class_exists ('TotallyBooked')) {
		add_action('mbt_general_settings_render', 'mbt_totallybooked_settings_render');
		mbt_add_custom_page('mbt_totallybooked_import', 'mbt_render_totallybooked_books_import_page');
	}
}

function mbt_render_totallybooked_books_import_page() {
	$books = mbt_totallybooked_books_import();

	?>
		<div class="wrap mbt_settings">
			<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('Totally Booked Book Import', 'mybooktable'); ?></h2>
			<h3><?php _e('The following books were successfully imported:', 'mybooktable'); ?></h3>
			<ul style="list-style:disc inside none;">
			<?php
				foreach($books as $book) {
					echo("<li>".$book."</li>");
				}
			?>
			</ul>
			<a href="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>" id="submit" class="button button-primary"><?php _e('Continue', 'mybooktable'); ?></a>
		</div>
	<?php
}

function mbt_totallybooked_settings_render() {
?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label><?php _e('Totally Booked Books Import', 'mybooktable'); ?></label></th>
				<td>
					<a href="<?php echo(mbt_get_custom_page_url('mbt_totallybooked_import')); ?>" id="submit" class="button button-primary"><?php _e('Import', 'mybooktable'); ?></a>
					<p class="description"><?php _e('Use this to import your existing books from Totally Booked into MyBookTable.', 'mybooktable'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}

function mbt_totallybooked_books_import() {
	$returns = array();
	$query = new WP_Query(array('post_type' => 'tb_book', 'posts_per_page' => -1));
	foreach ($query->posts as $book) {
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

		$buybuttons = array();
		foreach ($urls as $name => $store) {
			$link = get_post_meta($book->ID, $name, true);
			if(!empty($link)) { $buybuttons[] = array("display" => "featured", "store" => $store, "url" => $link); }
		}

		$isbn = get_post_meta($book->ID, 'isbn_number', true);
		$image_id = get_post_meta($book->ID, '_thumbnail_id', true);
		$mbt_imported_book_id = get_post_meta($book->ID, 'mbt_imported_book_id', true);


		$authors = mbt_import_totallybooked_taxonomy($book->ID, 'tb_author', 'mbt_author');
		$series = mbt_import_totallybooked_taxonomy($book->ID, 'tb_series', 'mbt_series');
		$genres = mbt_import_totallybooked_taxonomy($book->ID, 'tb_genre', 'mbt_genre');

		$returns[] = $book->post_title;

		if(!empty($mbt_imported_book_id) and ($mbt_imported_book = get_post($mbt_imported_book_id))) {
			$post_id = wp_update_post(array(
				'ID' => $mbt_imported_book_id,
				'post_title' => $book->post_title,
				'menu_order' => $book->menu_order,
			));
			$old_buybuttons = get_post_meta($post_id, "mbt_buybuttons", true);
			if(!empty($buybuttons) and empty($old_buybuttons)) { update_post_meta($post_id, "mbt_buybuttons", $buybuttons); }
			if(!empty($image_id)) { update_post_meta($post_id, 'mbt_book_image_id', $image_id); }
			update_post_meta($post_id, "mbt_unique_id", $isbn);
			wp_set_object_terms($post_id, $authors, "mbt_author");
			wp_set_object_terms($post_id, $series, "mbt_series");
			wp_set_object_terms($post_id, $genres, "mbt_genre");
		} else {
			$post_id = wp_insert_post(array(
				'post_title' => $book->post_title,
				'post_content' => $book->post_content,
				'menu_order' => $book->menu_order,
				'post_status' => 'publish',
				'post_type' => 'mbt_book'
			));
			if(!empty($buybuttons)) { update_post_meta($post_id, "mbt_buybuttons", $buybuttons); }
			if(!empty($image_id)) { update_post_meta($post_id, 'mbt_book_image_id', $image_id); }
			update_post_meta($post_id, "mbt_unique_id", $isbn);
			wp_set_object_terms($post_id, $authors, "mbt_author");
			wp_set_object_terms($post_id, $series, "mbt_series");
			wp_set_object_terms($post_id, $genres, "mbt_genre");

			update_post_meta($book->ID, "mbt_imported_book_id", $post_id);
		}
	}

	return $returns;
}

function mbt_import_totallybooked_taxonomy($post_id, $from_tax, $to_tax) {
	$returns = array();
	$terms = wp_get_object_terms($post_id, $from_tax);
	foreach ($terms as $term) {
		if(term_exists($term->name, $to_tax)) {
			$new_term = (array)get_term_by('name', $term->name, $to_tax);
		} else {
			$new_term = (array)wp_insert_term($term->name, $to_tax);
		}
		$returns[] = $new_term['term_id'];
	}
	return $returns;
}
