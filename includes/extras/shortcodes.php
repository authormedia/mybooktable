<?php

function mbt_shorcodes_init() {
	add_shortcode('mybooktable', 'mbt_mybooktable_shortcode');
	add_action('mbt_render_help_page', 'mbt_render_shordcode_help');

	if((current_user_can('edit_posts') || current_user_can('edit_pages')) && get_user_option('rich_editing') == 'true') {
		add_filter('mce_external_plugins', 'mbt_add_shortcode_tinymce_plugin');
		add_filter('mce_buttons', 'mbt_register_shortcode_button');
	}
}
add_action('mbt_init', 'mbt_shorcodes_init');

function mbt_add_shortcode_tinymce_plugin($plugin_array) {
	$plugin_array['mbt_shortcodes'] = plugins_url('js/shortcodes.js', dirname(dirname(__FILE__)));
	return $plugin_array;
}

function mbt_register_shortcode_button($buttons) {
	$buttons[] = "mbt_shortcodes_button";
	return $buttons;
}

/*---------------------------------------------------------*/
/* Shortcodes                                              */
/*---------------------------------------------------------*/

function mbt_mybooktable_shortcode($attrs) {
	global $wp_query, $posts, $post, $mbt_shortcode_old_wp_query, $mbt_shortcode_old_posts, $mbt_shortcode_old_post, $mbt_in_custom_page_content;

	if(!empty($mbt_in_custom_page_content)) { return ''; }
	if(mbt_is_mbt_page()) { return ''; }

	$max_books = empty($attr['max-books']) ? -1 : $attr['max-books'];

	$mbt_shortcode_old_post = $post;
	$mbt_shortcode_old_posts = $posts;
	$mbt_shortcode_old_wp_query = $wp_query;
	if(!empty($attrs['book'])) {
		$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'name' => $attrs['book']));
	} else if(!empty($attrs['author'])) {
		$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'mbt_author' => $attrs['author'], 'orderby' => 'menu_order', 'posts_per_page' => $max_books));
	} else if(!empty($attrs['series'])) {
		$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'mbt_series' => $attrs['series'], 'orderby' => 'menu_order', 'posts_per_page' => $max_books));
	} else if(!empty($attrs['genre'])) {
		$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'mbt_genre' => $attrs['genre'], 'orderby' => 'menu_order', 'posts_per_page' => $max_books));
	} else {
		$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'orderby' => 'menu_order', 'posts_per_page' => $max_books));
	}
	$post = empty($wp_query->post) ? null : $wp_query->post;
	$posts = $wp_query->posts;

	ob_start();

	$mbt_in_custom_page_content = true;
	if(is_singular('mbt_book')) {
		if(!empty($attrs['display']) and $attrs['display'] == 'summary') {
			echo('<div id="mbt-container">');
			include(mbt_locate_template('excerpt-book.php'));
			echo('</div>');
		} else {
			include(mbt_locate_template('single-book/content.php'));
		}
	} else {
		include(mbt_locate_template('archive-book/content.php'));
	}
	$mbt_in_custom_page_content = false;

	$output = ob_get_contents();
	ob_end_clean();

	$wp_query = $mbt_shortcode_old_wp_query;
	$posts = $mbt_shortcode_old_posts;
	$post = $mbt_shortcode_old_post;

	return $output;
}

function mbt_render_shordcode_help() {
?>
	<br><br><h2>MyBookTable Shortcodes</h2>
	<p>MyBookTable has a single [mybooktable] shortcode that can be used for a variety of purposes:</p>

	<h3>List all your books</h3>
	<pre>[mybooktable]</pre>

	<h3>List the books in a series</h3>
	<pre>[mybooktable series="lordoftherings"]</pre>
	<p>Note that you must use the <strong>slug</strong> of the series, not the name.<p/>

	<h3>List the books in a genre</h3>
	<pre>[mybooktable genre="fantasy"]</pre>
	<p>Note that you must use the <strong>slug</strong> of the genre, not the name.<p/>

	<h3>List the books written by an author</h3>
	<pre>[mybooktable author="jrrtolkien"]</pre>
	<p>Note that you must use the <strong>slug</strong> of the author, not the name.<p/>

	<h3>Display a single book</h3>
	<pre>[mybooktable book="the-fellowship-of-the-ring"]</pre>
	<p>Note that you must use the <strong>slug</strong> of the book, not the name.<p/>

	<h3>Display a single book summary</h3>
	<pre>[mybooktable book="the-fellowship-of-the-ring" display="summary"]</pre>
<?php
}