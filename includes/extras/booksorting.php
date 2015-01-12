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

function mbt_render_sort_books_page() {
	global $wpdb, $post;
	if(!empty($_REQUEST['mbt_book_order'])) {
		$data = json_decode(str_replace('\"', '"', $_REQUEST['mbt_book_order']));
		if(!empty($data) and is_object($data)) {
			$data = (array)$data;
			foreach($data as $key => $value) {
				$wpdb->update($wpdb->posts, array('menu_order' => $value), array('ID' => $key), array('%d'), array('%d'));
			}
		}
	}

	?>
	<div class="wrap mbt_sort_books">
		<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('Sort Books', 'mybooktable'); ?></h2>
		<form id="mbt_sort_books_form" method="post" action="<?php echo(admin_url('admin.php?page=mbt_sort_books')); ?>">
			<p class="submit">
				<input type="submit" name="save_settings" id="submit" class="button" value="<?php _e('Arrange Alphabetically', 'mybooktable'); ?>" onclick="return mbt_alphabetize_book_order();">
				&nbsp;&mdash;&nbsp;
				<input type="submit" name="save_settings" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'mybooktable'); ?>" onclick="return mbt_submit_book_order();">
			</p>
			<input id="mbt_book_order" name="mbt_book_order" type="hidden" value="">
			<?php
				$query = new WP_Query(array('post_type' => 'mbt_book', 'orderby' => 'menu_order', 'posts_per_page' => 499));
				if($query->have_posts()) {
					echo('<ul id="mbt_book_sorter">');
					while($query->have_posts()) {
						$query->the_post();
						echo('<li data-id="'.$post->ID.'" class="mbt_book">'.$post->post_title.'</li>');
					}
					echo('</ul>');
				} else {
					_e('No books to sort!', 'mybooktable');
				}
			?>
		</form>
	</div>
	<script type="text/javascript">
		jQuery.fn.reverse = [].reverse;

		function mbt_alphabetize_book_order() {
			jQuery("#mbt_book_sorter").append(jQuery("#mbt_book_sorter .mbt_book").detach().sort(function(a, b) {
				return jQuery(a).html().localeCompare(jQuery(b).html());
			}));
			return false;
		}

		function mbt_submit_book_order() {
			data = {};
			jQuery("#mbt_book_sorter .mbt_book").reverse().each(function(i, e) {
				data[jQuery(e).attr('data-id')] = i;
			});
			jQuery("#mbt_book_order").val(JSON.stringify(data));
			return true;
		}

		jQuery(document).ready(function() {
			jQuery("#mbt_book_sorter").sortable();
		});
	</script>
	<?php
}
