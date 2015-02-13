<?php

function mbt_booksorting_init() {
	add_filter('views_edit-mbt_book',  'mbt_add_sort_books_link');
	mbt_add_custom_page('mbt_sort_books', 'mbt_render_sort_books_page');
}
add_action('mbt_init', 'mbt_booksorting_init');

function mbt_add_sort_books_link($views) {
	$views['sorting'] = '<a href="'.mbt_get_custom_page_url('mbt_sort_books').'">'.__('Sort Books', 'mybooktable').'</a>';
	return $views;
}

function mbt_sort_books_page_sorter($books, $attributes) {
	$attribute = array_shift($attributes);
	$parts = explode('-', $attribute);
	$attrib_name = $parts[0];
	$attrib_dir = $parts[1];

	$sorted_books = array();
	foreach($books as $book) {
		$val = $book[$attrib_name];
		if(!isset($sorted_books[$val])) { $sorted_books[$val] = array(); }
		$sorted_books[$val][] = $book;
	}

	$keys = array_keys($sorted_books);
	natcasesort($keys);
	if($attrib_dir !== 'desc') { $keys = array_reverse($keys); }

	$final_books = array();
	foreach($keys as $key) {
		$section = $sorted_books[$key];
		if(count($section) > 1 and count($attributes) > 0) {
			$section = mbt_sort_books_page_sorter($section, $attributes);
		}
		foreach($section as $book) {
			$final_books[] = $book;
		}
	}

	return $final_books;
}

function mbt_render_sort_books_page() {
	global $wpdb, $post;
	if(!empty($_REQUEST['book_order']) and $_REQUEST['book_order'] == 'manual') {
		$data = json_decode(str_replace('\"', '"', $_REQUEST['mbt_manual_book_order']));
		if(!empty($data) and is_object($data)) {
			$data = (array)$data;
			foreach($data as $key => $value) {
				$wpdb->update($wpdb->posts, array('menu_order' => $value), array('ID' => $key), array('%d'), array('%d'));
			}
		}
	}

	if(!empty($_REQUEST['book_order']) and $_REQUEST['book_order'] == 'attribute') {
		$data = json_decode(str_replace('\"', '"', $_REQUEST['mbt_book_sorting_attributes']));
		if(!empty($data) and is_array($data)) {
			$query = new WP_Query(array('post_type' => 'mbt_book', 'posts_per_page' => -1));
			$books = array();
			foreach ($query->posts as $post) {
				$new_book = array();
				$new_book['ID'] = $post->ID;
				$new_book['title'] = $post->post_title;
				$new_book['date'] = strtotime($post->post_date_gmt);

				$author = '';
				$authors = get_the_terms($post->ID, 'mbt_author');
				if(!empty($authors) and !is_wp_error($authors)) {
					$sortfunc = function($a, $b) {
						$a = mbt_get_author_priority($a->term_id);
						$b = mbt_get_author_priority($b->term_id);
						return ($a > $b) ? -1 : (($a < $b) ? 1 : 0);
					};
					usort($authors, $sortfunc);
					if(!empty($authors[0]) and !is_wp_error($authors[0])) {
						$author = $authors[0]->name;
					}
				}
				$new_book['author'] = $author;

				$genre = '';
				$genres = get_the_terms($post->ID, 'mbt_genre');
				if(!empty($genres) and !is_wp_error($genres)) {
					if(!empty($genres[0]) and !is_wp_error($genres[0])) {
						$genre = $genres[0]->name;
					}
				}
				$new_book['genre'] = $genre;

				$tag = '';
				$tags = get_the_terms($post->ID, 'mbt_tag');
				if(!empty($tags) and !is_wp_error($tags)) {
					if(!empty($tags[0]) and !is_wp_error($tags[0])) {
						$tag = $tags[0]->name;
					}
				}
				$new_book['tag'] = $tag;

				$series = mbt_get_book_series($post->ID);
				if(!empty($series) and !is_wp_error($series)) { $series = $series->name; } else { $series = ''; }
				$new_book['series'] = $series;
				$new_book['series_order'] = strval(floatval(get_post_meta($post->ID, 'mbt_series_order', true)));
				$books[] = $new_book;
			}

			$books = mbt_sort_books_page_sorter($books, $data);

			for($i=0; $i < count($books); $i++) {
				$wpdb->update($wpdb->posts, array('menu_order' => $i), array('ID' => $books[$i]['ID']), array('%d'), array('%d'));
			}
		}
		mbt_update_setting('book_sorting_attributes', $_REQUEST['mbt_book_sorting_attributes']);
	}
	$book_sorting_attributes = mbt_get_setting('book_sorting_attributes');

	?>
	<div class="wrap mbt_sort_books">
		<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('Sort Books', 'mybooktable'); ?></h2>

		<div class="mbt_sorting_box">
			<div class="mbt_sorting_box_title"><?php _e('Attribute Sorting', 'mybooktable'); ?></div>
			<div class="mbt_sorting_box_content">
				<form id="mbt_sort_books_form" method="post" action="<?php echo(admin_url('admin.php?page=mbt_sort_books&book_order=attribute')); ?>">
					<input id="mbt_book_sorting_attributes" name="mbt_book_sorting_attributes" type="hidden" value="<?php echo(htmlentities(str_replace('\"', '"', $book_sorting_attributes))); ?>">
					<div id="mbt_book_attributes">
						<div id="mbt_book_attribute_adder"><span class="dashicons dashicons-plus"></span></div>
					</div>
					<input type="submit" name="save_settings" class="button button-primary" value="<?php _e('Sort', 'mybooktable'); ?>" onclick="return mbt_submit_book_sorting_attributes();">
				</form>
			</div>
		</div>

		<div class="mbt_sorting_box">
			<div class="mbt_sorting_box_title"><?php _e('Manual Sorting', 'mybooktable'); ?></div>
			<div class="mbt_sorting_box_content">
				<form id="mbt_sort_books_form" method="post" action="<?php echo(admin_url('admin.php?page=mbt_sort_books&book_order=manual')); ?>">
					<?php
						$query = new WP_Query(array('post_type' => 'mbt_book', 'orderby' => 'menu_order', 'posts_per_page' => 300));
						if($query->have_posts()) {
							if(count($query->posts) > 300) {
								echo('<div class="mbt_sorting_error">'.__('Manual book sorting is not supported for more than 300 books. Please use Attribute Sorting.', 'mybooktable').'</div>');
							} else {
								echo('<input type="submit" name="save_settings" class="button button-primary" value="'.__('Save Changes', 'mybooktable').'" onclick="return mbt_submit_manual_book_order();">');
								echo('<input id="mbt_manual_book_order" name="mbt_manual_book_order" type="hidden" value="">');
								echo('<ul id="mbt_manual_book_sorter">');
								while($query->have_posts()) {
									$query->the_post();
									echo('<li data-id="'.$post->ID.'" class="mbt_book">'.$post->post_title.'</li>');
								}
								echo('</ul>');
							}
						} else {
							_e('No books to sort!', 'mybooktable');
						}
					?>
				</form>
			</div>
		</div>

	</div>
	<script type="text/javascript">

	/*---------------------------------------------------------*/
	/* Attribute Sorting                                       */
	/*---------------------------------------------------------*/

	function mbt_submit_book_sorting_attributes() {
		data = [];
		jQuery("#mbt_book_attributes .mbt_book_attribute").each(function(i, e) {
			data.push(jQuery(e).find('.mbt_book_attribute_selector').val()+'-'+jQuery(e).find('.mbt_book_direction_selector').val());
		});
		jQuery("#mbt_book_sorting_attributes").val(JSON.stringify(data));
		return true;
	}

	function mbt_add_book_sorting_attribute(attribute) {
		attribute = (typeof attribute === 'undefined') ? 'title-asc' : attribute;
		parts = attribute.split('-');
		attrib_name = parts[0];
		attrib_dir = parts[1];

		var element = '';
		element += '<div class="mbt_book_attribute">';
		if(jQuery('.mbt_book_attribute').length < 1) {
			element += 'Sort by: ';
		} else {
			element += 'Then: ';
		}
		var attributes = {
			'title': '<?php _e('Post Title', 'mybooktable'); ?>',
			'date': '<?php _e('Post Date', 'mybooktable'); ?>',
			'author': '<?php _e('Author', 'mybooktable'); ?>',
			'genre': '<?php _e('Genre', 'mybooktable'); ?>',
			'genre': '<?php _e('Tag', 'mybooktable'); ?>',
			'series': '<?php _e('Series', 'mybooktable'); ?>',
			'series_order': '<?php _e('Series Order', 'mybooktable'); ?>',
		};
		element += '<select name="mbt_book_attribute_selector" class="mbt_book_attribute_selector">';
		for(var name in attributes) {
			element += '<option value="'+name+'" '+(attrib_name === name ? 'selected="selected"' : '')+'>'+attributes[name]+'</option>';
		}
		element += '</select>';
		element += '<select name="mbt_book_direction_selector" class="mbt_book_direction_selector">';
		element += '<option value="asc" '+(attrib_dir === 'asc' ? 'selected="selected"' : '')+'><?php _e('Ascending', 'mybooktable'); ?></option>';
		element += '<option value="desc" '+(attrib_dir === 'desc' ? 'selected="selected"' : '')+'><?php _e('Descending', 'mybooktable'); ?></option>';
		element += '</select>';
		if(jQuery('.mbt_book_attribute').length >= 1) {
			element += '<div class="mbt_book_attribute_remover">';
			element += '<span class="dashicons dashicons-minus"></span>';
			element += '</div>';
		}
		element += '</div>';
		jQuery('#mbt_book_attribute_adder').before(jQuery(element));
	}

	jQuery(document).ready(function() {
		jQuery('#mbt_book_attribute_adder').click(function() { mbt_add_book_sorting_attribute(); });

		jQuery('#mbt_book_attributes').on('click', '.mbt_book_attribute_remover', function() {
			jQuery(this).parents('.mbt_book_attribute').remove();
		});

		if(jQuery('#mbt_book_sorting_attributes').val()) {
			jQuery.each(JSON.parse(jQuery('#mbt_book_sorting_attributes').val()), function(i, e) {
				mbt_add_book_sorting_attribute(e);
			});
		} else {
			mbt_add_book_sorting_attribute();
		}
	});

	/*---------------------------------------------------------*/
	/* Manual Sorting                                          */
	/*---------------------------------------------------------*/

	jQuery.fn.reverse = [].reverse;

	function mbt_submit_manual_book_order() {
		data = {};
		jQuery("#mbt_manual_book_sorter .mbt_book").reverse().each(function(i, e) {
			data[jQuery(e).attr('data-id')] = i;
		});
		jQuery("#mbt_manual_book_order").val(JSON.stringify(data));
		return true;
	}

	jQuery(document).ready(function() {
		jQuery("#mbt_manual_book_sorter").sortable();
	});

	</script>
	<?php
}
