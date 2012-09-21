<?php
/*
Plugin Name: Book Table
Plugin URI: http://www.castlemediagroup.com
Description: A simple store plugin for books, allowing you to integrate with external shopping carts and bookstore websites.
Author: Jim Camomile
Version: 2.0
*/

//load main options from options panel
$bt_main_options = get_option("button-store-options_options"); 

require_once("includes/widgets.php");
require_once("includes/content_output.php");
require_once("includes/admin_pages.php");
require_once("includes/post_types.php");
require_once("includes/metaboxes.php");



/*---------------------------------------------------------*/
/* General                                                 */
/*---------------------------------------------------------*/

//enqueue global plugin styles
add_action('wp_head', 'bt_enqueue_styles');
function bt_enqueue_styles(){
	wp_enqueue_style('book-table-style', plugins_url('/css/book-table-style.css', __FILE__));
}

//make a new archive for category pages
function bt_load_store_cat_template($archive_template) {
	global $bt_main_options;

	//bail if this option is turned off
	if($bt_main_options['use_templates'] != 'on'){return $archive_template;}

	//try approach that detects prod cats AND product index
	if (get_post_type() == 'min_products' and is_archive()) {
		// first check in the templatepath and stylesheet path, in case the templates were moved there
		$locatedtemplate = locate_template('store-cat-template.php');
		if(empty($locatedtemplate)) {
			$archive_template = dirname(__FILE__).'/templates/store-cat-template.php'; //load the default template in the plugin
		} else {
			$archive_template = $locatedtemplate; //found in Theme, load it
		}
	}
	return $archive_template; 
}
add_filter('archive_template', 'bt_load_store_cat_template');

// change the number of posts per page for the product archives
function bt_set_product_number($query) {
	global $bt_main_options; 
	$postsper = !empty($bt_main_options['posts_per_page']) ? $bt_main_options['posts_per_page'] : 10; // if no option is set then default is 10
	if(get_post_type() != 'min_products' and $query->is_main_query()) { // is only for min_product archives and only on main query
	   $query->query_vars['posts_per_page'] = $postsper;
	}
}
add_action('pre_get_posts', 'bt_set_product_number');





/*---------------------------------------------------------*/
/* Inexplicable Code                                       */
/*---------------------------------------------------------*/

//???
remove_filter('pre_term_description', 'wp_filter_kses');
remove_filter('term_description', 'wp_kses_data');

//change the excerpt more ... This is used only for the sake of the template but it is generally a good idea
function new_excerpt_more($more){return ' &hellip;';}
add_filter('excerpt_more', 'new_excerpt_more');

//hide post-meta div or p and comments box for products in archives. Styles may need adjusting per theme
function hide_product_meta() {
	if('"' != get_post_type()) {return;}
	echo('<style type="text/css"> .post-meta, .comments {display: none;} </style>');
}
add_action('wp_head', 'hide_product_meta');





/*---------------------------------------------------------*/
/* Breadcrumbs                                             */
/*---------------------------------------------------------*/

add_action('init', 'bt_load_breadcrumbs'); // breadcrumbs
function bt_load_breadcrumbs() {
	global $bt_main_options;
	if($bt_main_options['is_woo'] == 'on') { // if using woo, then hook breadcrumbs in the very nice woo_loop_before action hook.
		add_action('woo_loop_before', 'bt_show_breadcrumbs');
	} else { // settle to add breadcrumbs to a more native hook in WP, though it displays better in some templates than others
		add_action('get_template_part_content', 'bt_show_breadcrumbs'); // breadcrumbs
	}
}

function bt_show_breadcrumbs() {
	echo(bt_get_breadcrumbs());
}

function bt_get_breadcrumbs() {
	global $wp_query, $bt_main_options;
	$theoption = $bt_main_options['show_breadcrumbs'];
	
	if ($theoption != 'on') { // check to see that breadcrumbs are on, if not, scram
		return;
	}
	$taxonomy_obj = $wp_query->get_queried_object();
	$type = get_query_var('post_type');
	$termtax = $taxonomy_obj->taxonomy;
	if ($type != 'min_products' && $termtax != 'product_category'){
		return;
	}
	//print_r($taxonomy_obj);
	$sep = '<span class="sep">&raquo;</span>';
	$main = '<a href="#">Books </a>'.$sep.' ';
	$store_bc = '<div id="buttonstore_breadcrumbs">';
	$store_bc .= $main;
	// category stuff
	if($termtax == 'product_category'){
		$termname = $taxonomy_obj->name;
		$termslug = $taxonomy_obj->slug;
		$termurl = '<a href="/'.$termtax.'/'.$termslug.'/" >'.$termname.'</a>';
		$store_bc .= $termurl;
	} 
	//	$termtax = $taxonomy_obj->taxonomy;
	// single stuff
	if($type == 'min_products'){
		$booktitle = $taxonomy_obj->post_title;
		$store_bc .= $booktitle;
	}
	// $bookslug = $taxonomy_obj->post_name;
	// categories.. should we add these?

	// Finish breadcrumbs
		$store_bc .= '</div>';
		return $store_bc;
	// End Add Breadcrumbs
}





/*---------------------------------------------------------*/
/* Utility functions                                       */
/*---------------------------------------------------------*/

// Limit words
function string_limit_words($string, $word_limit)
{
	$words = explode(' ', $string, ($word_limit + 1));
	if(count($words) > $word_limit){ array_pop($words); }
	return implode(' ', $words);
}

//Truncate by number of characters, placing ellipses after last word
function ttruncat($text, $length){
	if(strlen($text) > $length){
		$text = substr($text, 0, $length);
		return substr($text, 0, strrpos($text, " "))."&hellip;";
	}
	return $text;
}

//helper function to find book images
function bt_get_book_image($width, $height, $class='thumbnail', $link='src') { //getcastleimage, castleimage
	global $post;
	if (function_exists('woo_image')){
		//if woo themes woo_image is in play, by all means use it
		return woo_image('key=image&size=thumbnail&link='.$link.'&class='.$class.'&width='.$width.'&noheight=true&return=1');

	} elseif(has_post_thumbnail()) {
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
		$theimage = $matches[1][0];
		if(empty($first_img)){$theimage = "/images/default.jpg";} //Defines a default image

		return get_the_post_thumbnail(array($width, $height), array(
			'src'	=> $theimage,
			'class'	=> $class,
			'alt'	=> trim(strip_tags($post->post_title)),
			'title'	=> trim(strip_tags($post->post_title)),
		)); 
	}
}

function bt_show_book_image($width, $height, $class='thumbnail', $link='src') {
	echo(bt_get_book_image($width, $height, $class, $link));
}
