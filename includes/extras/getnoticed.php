<?php

function mbt_getnoticed_init() {
	add_action('after_setup_theme', 'mbt_getnoticed_compat', 20);
	add_filter('mbt_importers', 'mbt_add_getnoticed_importer');
}
add_action('mbt_init', 'mbt_getnoticed_init');

function mbt_getnoticed_compat() {
	if(function_exists('getnoticed_setup')) {
		remove_action('init', 'getnoticed_types_book_init');
		add_filter('pre_get_posts', 'mbt_getnoticed_post_types_unindex', 20);
		add_action('wp_head', 'mbt_add_getnoticed_css');
	}
}

function mbt_getnoticed_post_types_unindex($query) {
	if((is_home() || (is_archive() && !is_post_type_archive())) && $query->is_main_query()) {
		$post_type = $query->get('post_type');
		if(is_array($post_type) and in_array('book', $post_type)) { unset($post_type[array_search('book', $post_type)]); }
		else if($post_type === 'book') { $post_type = 'mbt_books'; }
		$query->set('post_type', $post_type);
	}
}

function mbt_add_getnoticed_css() {
	global $_THEME;
	$image_size = mbt_get_setting('image_size');
	echo('<style type="text/css">');
	echo('.mybooktable h1 {');
	echo('    font-family: '.$_THEME->get_fontstack($_THEME->getord('family', 'title-font')).';');
	echo('    font-size: '.$_THEME->getord('size', 'title-font').';');
	echo('    line-height: '.$_THEME->getord('line-height', 'title-font').';');
	$values = $_THEME->getord('options', 'title-font');
	if(in_array('bold', $values)) { echo('font-weight: bold;'); }
	if(in_array('italic', $values)) { echo('font-style: italic;'); }
	echo('    color: '.$_THEME->getord('title-color').';');
	echo('}');
	echo('.mbt-featured-book-widget {');
	echo('    padding: 0px;');
	echo('}');
	echo('.mbt-featured-book-widget .mbt-featured-book-widget-book {');
	echo('    padding: 20px;');
	echo('    border-bottom: 1px solid #d6d6d6;');
	echo('}');
	echo('.mbt-featured-book-widget .mbt-featured-book-widget-book:last-child {');
	echo('    border-bottom: none;');
	echo('}');
	echo('</style>');
}

function mbt_add_getnoticed_importer($importers) {
	$exists = function_exists('getnoticed_setup');

	$importers['getnoticed'] = array(
		'name' => __('GetNoticed', 'mybooktable'),
		'desc' => __('Import your books from the GetNoticed theme.', 'mybooktable'),
		'callback' => 'mbt_render_getnoticed_books_import_page',
		'disabled' => ($exists ? '' : 'GetNoticed theme not detected'),
	);
	return $importers;
}

function mbt_render_getnoticed_books_import_page() {
	$importing = !empty($_GET['mbt_confirm_import']);

	$books = mbt_getnoticed_get_books();
	if($importing) {
		mbt_track_event('book_import_getnoticed');
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
			<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('GetNoticed Book Import', 'mybooktable'); ?></h2>

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
				<a href="<?php echo(admin_url('admin.php?page=mbt_import&mbt_import_type=getnoticed&mbt_confirm_import=1')); ?>" class="import-submit button button-primary"><?php _e('Import', 'mybooktable'); ?></a>
				<div id="mbt-book-import-spinner-box">Please wait, your books are importing...<div id="mbt-book-import-spinner"></div></div>
				<script type="text/javascript">
					jQuery('.mbt_book_importer .import-submit').click(function(e) {
						jQuery('#mbt-book-import-spinner-box').show();
					});
				</script>
			<?php } ?>
		</div>
	<?php
}

function mbt_getnoticed_get_books() {
	$books = array();
	$query = new WP_Query(array('post_type' => 'book', 'posts_per_page' => -1));
	foreach($query->posts as $book) {
		$book_meta = get_post_meta($book->ID, 'getnoticed_meta_book', true);

		$new_book = array();
		$new_book['source_id'] = $book->ID;
		$new_book['title'] = $book->post_title;
		$new_book['content'] = $book->post_content;
		$new_book['excerpt'] = $book->post_excerpt;
		$new_book['authors'] = array();
		if(!empty($book_meta['bookauthor'])) { $new_book['authors'][] = $book_meta['bookauthor']; }
		$new_book['unique_id'] = $book_meta['asin'];
		$new_book['buybuttons'] = array();
		if(!empty($book_meta['link'])) { $new_book['buybuttons'][] = array('display' => 'featured', 'store' => 'amazon', 'url' => $book_meta['link']); }
		$new_book['publisher_name'] = $book_meta['publisher'];
		$new_book['publication_year'] = $book_meta['year'];
		$new_book['image_id'] = get_post_meta($book->ID, '_thumbnail_id', true);
		$new_book['imported_book_id'] = get_post_meta($book->ID, 'mbt_imported_book_id', true);

		$books[] = $new_book;
	}

	return $books;
}
