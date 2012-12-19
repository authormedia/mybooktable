<?php
/*
Plugin Name: My Book Table
Plugin URI: http://www.mybooktable.com
Description: A simple store plugin for books, allowing you to integrate with external shopping carts and bookstore websites.
Author: Castle Media Group
Version: 0.1.0
*/

//load main settings from settings panel
require_once("includes/install.php");
$mbt_main_settings = get_option("mbt_main_settings");

require_once("includes/affiliates.php");
require_once("includes/plugin_widgets.php");
require_once("includes/content_output.php");
require_once("includes/admin_pages.php");
require_once("includes/post_types.php");
require_once("includes/metaboxes.php");



/*---------------------------------------------------------*/
/* General                                                 */
/*---------------------------------------------------------*/

//enqueue frontend plugin styles
add_action('wp_enqueue_scripts', 'mbt_enqueue_styles');
function mbt_enqueue_styles() {
	wp_enqueue_style('book-table-style', plugins_url('css/frontend-style.css', __FILE__));
}

//make a new archive for archive pages
function mbt_load_book_archive_template($archive_template) {
	global $mbt_main_settings;

	if(is_post_type_archive('mbt_books') or is_tax('mbt_genres') or is_tax('mbt_series') or is_tax('mbt_themes')) {
		$locatedtemplate = locate_template('archive-books.php');
		$archive_template = empty($locatedtemplate) ? dirname( __FILE__ ).'/templates/archive-books.php' : $locatedtemplate;
	}

	return $archive_template; 
}
add_filter('archive_template', 'mbt_load_book_archive_template');

// change the number of posts per page for the book archives
function mbt_set_books_posts_per_page($query) {
	global $mbt_main_settings;
	if(get_post_type() != 'mbt_books' and $query->is_main_query()) { // is only for min_book archives and only on main query
		$query->query_vars['posts_per_page'] = !empty($mbt_main_settings['posts_per_page']) ? $mbt_main_settings['posts_per_page'] : get_option('posts_per_page');
	}
}
add_action('pre_get_posts', 'mbt_set_books_posts_per_page');

function mbt_is_seo_active() {
	global $mbt_main_settings;
	$active = !$mbt_main_settings['disable_seo'];
	if(defined('WPSEO_FILE')) { $active = false; }
	return apply_filters('mbt_is_seo_active', $active);
}

function mbt_bookstore_shortcode( $atts ) {
?>
	<?php $query = new WP_Query(array('post_type' => 'mbt_books')); ?>
	<?php if($query->have_posts()) { ?>

		<?php while($query->have_posts()){ $query->the_post(); ?>

			<div <?php post_class(); ?>>

				<?php 
					global $post;
					mbt_show_book_image($post, 175, 250, 'thumbnail alignleft');
					echo('<h3 class="title"><a href="'.get_permalink().'">'.get_the_title().'</a> </h3>');
				?>

				<div class="entry">
					<?php the_excerpt(); ?>
				</div>

			</div><!-- end .post -->

		<?php } ?>

	<?php } else { ?>
		Sorry, nothing here.
	<?php } ?>

<?php
	wp_reset_query();
}
add_shortcode('mbt_bookstore', 'mbt_bookstore_shortcode');





/*---------------------------------------------------------*/
/* Utility functions                                       */
/*---------------------------------------------------------*/

//Truncate by number of characters, placing ellipses after last word
function string_limit_chars($text, $length){
	if(strlen($text) > $length){
		$text = substr($text, 0, $length);
		return substr($text, 0, strrpos($text, " ")).do_filters("mbt_string_limit_end", "&hellip;");
	}
	return $text;
}

//helper function to find book images
function mbt_get_book_image($post, $width, $height, $class='thumbnail', $link='src') {
	if (function_exists('woo_image')){
		//if woo themes woo_image is in play, by all means use it
		return woo_image('key=image&size=thumbnail&link='.$link.'&class='.$class.'&width='.$width.'&noheight=true&return=1');

	} elseif(has_post_thumbnail($post->ID)) {
		//else if there is a featured image to grab, use that
		return get_the_post_thumbnail($post->ID, array($width, $height), array(
			'class'	=> $class,
			'alt'	=> trim(strip_tags($post->post_title)),
			'title'	=> trim(strip_tags($post->post_title)),
		));

	} else {
		//last straw, use homegrown image finding script from cats that code
		$matches = array();
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
		$theimage = isset($matches[1]) ? (isset($matches[1][0]) ? $matches[1][0] : '') : '';
		if(empty($theimage)){$theimage = "/images/default.jpg";} //Defines a default image

		return get_the_post_thumbnail(array($width, $height), array(
			'src'	=> $theimage,
			'class'	=> $class,
			'alt'	=> trim(strip_tags($post->post_title)),
			'title'	=> trim(strip_tags($post->post_title)),
		));
	}
}

function mbt_show_book_image($post, $width, $height, $class='thumbnail', $link='src') {
	echo(mbt_get_book_image($post, $width, $height, $class, $link));
}