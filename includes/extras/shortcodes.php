<?php

function mbt_shorcodes_init() {
	add_shortcode('mybooktable', 'mbt_mybooktable_shortcode');
	add_action('mbt_render_help_page', 'mbt_render_shordcode_help');
}
add_action('mbt_init', 'mbt_shorcodes_init');

/*---------------------------------------------------------*/
/* Shortcodes                                              */
/*---------------------------------------------------------*/

function mbt_mybooktable_shortcode($attrs) {
	global $wp_query, $posts, $post, $mbt_shortcode_old_wp_query, $mbt_shortcode_old_posts, $mbt_shortcode_old_post;

	$max_books = empty($attr['max-books']) ? -1 : $attr['max-books'];

	$mbt_shortcode_old_post = $post;
	$mbt_shortcode_old_posts = $posts;
	$mbt_shortcode_old_wp_query = $wp_query;
	/*if(!empty($attrs['book'])) {
		$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'name' => $attrs['book']));
	} else */
	if(!empty($attrs['author'])) {
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

	if(is_singular('mbt_book')) {
		do_action('mbt_before_single_book');
		do_action('mbt_single_book_images');
		?><div class="mbt-book-right"><?php
		do_action('mbt_single_book_title');
		do_action('mbt_single_book_price');
		do_action('mbt_single_book_meta');
		do_action('mbt_single_book_blurb');
		do_action('mbt_single_book_buybuttons');
		?></div><?php
		do_action('mbt_single_book_overview');
		do_action('mbt_single_book_socialmedia');
		do_action('mbt_single_book_series');
		do_action('mbt_after_single_book');
	} else {
		do_action('mbt_before_book_archive');
		if(have_posts()) {
			do_action('mbt_book_archive_loop');
		} else {
			do_action('mbt_book_archive_no_results');
		}
		do_action('mbt_after_book_archive');
	}

	$output = ob_get_contents();
	ob_end_clean();

	$wp_query = $mbt_shortcode_old_wp_query;
	$posts = $mbt_shortcode_old_posts;
	$post = $mbt_shortcode_old_post;

	return $output;
}

function mbt_render_shordcode_help() {
?>
	<br><br><h2>Shortcodes</h2>
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
<?php
}