<?php

function mbt_getnoticed_init() {
	add_action('after_setup_theme', 'mbt_getnoticed_compat', 20);
}
add_action('mbt_init', 'mbt_getnoticed_init');

function mbt_getnoticed_compat() {
	if(function_exists('getnoticed_setup')) {
		remove_action('init', 'getnoticed_types_book_init');
		add_filter('pre_get_posts', 'mbt_getnoticed_post_types_unindex', 20);
		add_action('mbt_general_settings_render', 'mbt_getnoticed_settings_render');
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

function mbt_render_getnoticed_books_import_page() {
	$books = mbt_getnoticed_books_import();

	?>
		<div class="wrap mbt_settings">
			<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('GetNoticed Book Import', 'mybooktable'); ?></h2>
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

function mbt_getnoticed_settings_render() {
?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label><?php _e('GetNoticed Books Import', 'mybooktable'); ?></label></th>
				<td>
					<a href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_getnoticed_books_import=1')); ?>" id="submit" class="button button-primary"><?php _e('Import', 'mybooktable'); ?></a>
					<p class="description"><?php _e('Use this to import your existing books from GetNoticed into MyBookTable.', 'mybooktable'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}

function mbt_getnoticed_books_import() {
	$returns = array();
	$query = new WP_Query(array('post_type' => 'book', 'posts_per_page' => -1));
	foreach ($query->posts as $book) {
		$book_meta = get_post_meta($book->ID, 'getnoticed_meta_book', true);
		$author = $book_meta['bookauthor'];
		$asin = $book_meta['asin'];
		$link = $book_meta['link'];
		$publisher = $book_meta['publisher'];
		$year = $book_meta['year'];
		$mbt_imported_book_id = get_post_meta($book->ID, 'mbt_imported_book_id', true);

		$returns[] = $book->post_title;

		if(!empty($mbt_imported_book_id) and ($mbt_imported_book = get_post($mbt_imported_book_id))) {
			$post_id = wp_update_post(array(
				'ID' => $mbt_imported_book_id,
				'post_title' => $book->post_title,
			));
			$old_buybuttons = get_post_meta($post_id, "mbt_buybuttons", true);
			if(!empty($link) and empty($old_buybuttons)) { update_post_meta($post_id, "mbt_buybuttons", unserialize('a:1:{i:0;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:6:"amazon";s:3:"url";s:'.strlen($link).':"'.$link.'";}}')); }
			$image_id = get_post_meta($book->ID, '_thumbnail_id', true);
			if(!empty($image_id)) { update_post_meta($post_id, 'mbt_book_image_id', $image_id); }
			update_post_meta($post_id, "mbt_unique_id", $asin);
			update_post_meta($post_id, "mbt_publisher_name", $publisher);
			update_post_meta($post_id, "mbt_publication_year", $year);
		} else {
			$post_id = wp_insert_post(array(
				'post_title' => $book->post_title,
				'post_content' => $book->post_content,
				'post_excerpt' => $book->post_excerpt,
				'post_status' => 'publish',
				'post_type' => 'mbt_book'
			));
			if(!empty($link)) { update_post_meta($post_id, "mbt_buybuttons", unserialize('a:1:{i:0;a:3:{s:7:"display";s:8:"featured";s:5:"store";s:6:"amazon";s:3:"url";s:'.strlen($link).':"'.$link.'";}}')); }
			$image_id = get_post_meta($book->ID, '_thumbnail_id', true);
			if(!empty($image_id)) { update_post_meta($post_id, 'mbt_book_image_id', $image_id); }
			update_post_meta($post_id, "mbt_unique_id", $asin);
			update_post_meta($post_id, "mbt_publisher_name", $publisher);
			update_post_meta($post_id, "mbt_publication_year", $year);

			update_post_meta($book->ID, "mbt_imported_book_id", $post_id);
		}
	}

	return $returns;
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
