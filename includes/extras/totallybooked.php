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
		'callback' => 'mbt_render_totallybooked_books_import_page',
		'disabled' => ($exists ? '' : 'Totally Booked plugin not detected'),
	);
	return $importers;
}

function mbt_render_totallybooked_books_import_page() {
	$importing = !empty($_GET['mbt_confirm_import']);

	$books = mbt_totallybooked_get_books();
	if($importing) {
		mbt_track_event('book_import_totallybooked');
		echo('<div id="mbt-book-import-progress">');
		echo('<h3>'.__('Please wait, your books are importing...', 'mybooktable').'</h3>');
		echo('<div id="mbt-book-import-progress-bar"><div id="mbt-book-import-progress-bar-inner"></div></div>');
		wp_ob_end_flush_all(); flush();

		$percent = 0; $percent_per_book = 100.0/count($books);
		foreach($books as $key => $book) {
			$books[$key]['imported_book_id'] = mbt_import_book($book);
			$percent += $percent_per_book;
			echo('<script type="text/javascript">jQuery("#mbt-book-import-progress-bar-inner").css("width", "'.$percent.'%")</script>');
			wp_ob_end_flush_all(); flush();
		}
		sleep(1);

		echo('</div>');
		echo('<script type="text/javascript">jQuery("#mbt-book-import-progress").hide()</script>');
	}

	?>
		<div class="wrap mbt_book_importer">
			<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('Totally Booked Book Import', 'mybooktable'); ?></h2>

			<?php if($importing) { ?>
				<h3><?php _e('The following books were successfully imported:', 'mybooktable'); ?></h3>
			<?php } else { ?>
				<h3><?php _e('The following books will be imported:', 'mybooktable'); ?></h3>
			<?php } ?>

			<ul class="mbt_imported_books">
			<?php
				foreach($books as $book) {
					echo('<li><a href="'.get_permalink($book['imported_book_id']).'" target="_blank">'.$book['title'].'</a></li>');
				}
			?>
			</ul>

			<?php if($importing) { ?>
				<a href="<?php echo(admin_url('edit.php?post_type=mbt_book')); ?>" class="button button-primary"><?php _e('Continue', 'mybooktable'); ?></a>
			<?php } else { ?>
				<h3><?php _e('Are you sure you want to import these books?', 'mybooktable'); ?></h3>
				<a href="<?php echo(admin_url('admin.php?page=mbt_import&mbt_import_type=totallybooked&mbt_confirm_import=1')); ?>" class="button button-primary"><?php _e('Import', 'mybooktable'); ?></a>
			<?php } ?>
		</div>
	<?php
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
			if(!empty($link)) { $new_book['buybuttons'][] = array('display' => 'featured', 'store' => $store, 'url' => $link); }
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
