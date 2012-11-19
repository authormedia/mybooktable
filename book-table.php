<?php
/*
Plugin Name: My Book Table
Plugin URI: http://www.mybooktable.com
Description: A simple store plugin for books, allowing you to integrate with external shopping carts and bookstore websites.
Author: Castle Media Group
Version: 0.1.0
*/

//load main options from options panel
$mbt_main_options = get_option("cap_mbt_options");

require_once("includes/affiliates.php");
require_once("includes/plugin_widgets.php");
require_once("includes/content_output.php");
require_once("includes/admin_pages.php");
require_once("includes/post_types.php");
require_once("includes/metaboxes.php");



/*---------------------------------------------------------*/
/* General                                                 */
/*---------------------------------------------------------*/

//enqueue global plugin styles
add_action('wp_head', 'mbt_enqueue_styles');
function mbt_enqueue_styles(){
	wp_enqueue_style('book-table-style', plugins_url('style.css', __FILE__));
}

//make a new archive for category pages
function mbt_load_store_cat_template($archive_template) {
	global $mbt_main_options;

	//bail if this option is turned off
	if($mbt_main_options['use_templates'] != 'on'){return $archive_template;}

	if (get_post_type() == 'mbt_products' and is_archive()) {
		// first check in the templatepath and stylesheet path, in case the templates were moved there
		$locatedtemplate = locate_template('archive-products.php');
		if(empty($locatedtemplate)) {
			$archive_template = dirname(__FILE__).'/templates/archive-products.php'; //load the default template in the plugin
		} else {
			$archive_template = $locatedtemplate; //found in Theme, load it
		}
	}
	return $archive_template; 
}
add_filter('archive_template', 'mbt_load_store_cat_template');

// change the number of posts per page for the product archives
function mbt_set_products_posts_per_page($query) {
	global $mbt_main_options;
	if(get_post_type() != 'mbt_products' and $query->is_main_query()) { // is only for min_product archives and only on main query
	   $query->query_vars['posts_per_page'] = !empty($mbt_main_options['posts_per_page']) ? $mbt_main_options['posts_per_page'] : 10;
	}
}
add_action('pre_get_posts', 'mbt_set_products_posts_per_page');





/*---------------------------------------------------------*/
/* Breadcrumbs                                             */
/*---------------------------------------------------------*/

add_action('init', 'mbt_load_breadcrumbs');
function mbt_load_breadcrumbs() {
	global $mbt_main_options;
	if($mbt_main_options['show_breadcrumbs'] == 'on') {
		if($mbt_main_options['is_woo'] == 'on') { // if using woo, then hook breadcrumbs in the very nice woo_loop_before action hook.
			add_action('woo_loop_before', 'mbt_show_breadcrumbs');
		} else { // settle to add breadcrumbs to a more native hook in WP, though it displays better in some templates than others
			add_action('get_template_part_content', 'mbt_show_breadcrumbs');
		}
	}
}

function mbt_show_breadcrumbs() {
	echo(mbt_get_breadcrumbs());
}

function mbt_get_breadcrumbs() {
	global $wp_query, $mbt_main_options;

	if(get_query_var('post_type')!= 'mbt_products'){return;} //only add for custom post type

	$taxonomy_obj = $wp_query->get_queried_object();

	if($taxonomy_obj->taxonomy != 'mbt_product_category'){return;} //only add for product categories

	$output = '<div class="mbt-breadcrumbs">';
	$output .= '<a href="/products/">Books</a> <span class="sep">&raquo;</span> ';
	$output .= '<a href="/product_category/'.$taxonomy_obj->slug.'/" >'.$taxonomy_obj->name.'</a> <span class="sep">&raquo;</span> ';
	$output .= '</div>';
	return apply_filters("mbt_breadcrumbs", $output);
}

/*---------------------------------------------------------*/
/* Utility functions                                       */
/*---------------------------------------------------------*/

//Truncate by number of characters, placing ellipses after last word
function string_limit_chars($text, $length){
	if(strlen($text) > $length){
		$text = substr($text, 0, $length);
		return substr($text, 0, strrpos($text, " "))."&hellip;";
	}
	return $text;
}

//helper function to find product images
function mbt_get_product_image($post, $width, $height, $class='thumbnail', $link='src') {
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

function mbt_show_product_image($post, $width, $height, $class='thumbnail', $link='src') {
	echo(mbt_get_product_image($post, $width, $height, $class, $link));
}
