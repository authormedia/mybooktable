<?php

/*---------------------------------------------------------*/
/* Custom Post Types and Taxonomies                        */
/*---------------------------------------------------------*/

add_action('init', 'mbt_create_post_types_and_taxonomies');
function mbt_create_post_types_and_taxonomies()
{
	register_post_type('mbt_books', array(
		'labels' => array(
			'name' => 'MyBookTable',
			'singular_name' => 'Book',
			'all_items' => 'Books',
			'add_new' => 'Add New Book',
			'add_new_item' => 'Add New Book',
			'new_item_name' => 'New Book',
			'edit_item' => 'Edit Book',
			'view_item' => 'View Book',
			'update_item' => 'Update Book',
			'search_items' => 'Search Books',
			'parent_item' => 'Parent Book',
			'parent_item_colon' => 'Parent Book:'
		),
		'public' => true,
		'show_ui' => true,
		'query_var' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_icon' => plugins_url('images/option-icon-cart.png', dirname(__FILE__)),
		'menu_position' => 5, 
		'exclude_from_search' => false,
		'has_archive' => true,
		'supports' => array('title', 'thumbnail'),
		'rewrite' => array('slug' => 'books')
	));

	register_taxonomy('mbt_authors', 'mbt_books', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => 'Authors',
			'singular_name' => 'Author',
			'all_items' => 'All Authors',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Authors',
			'new_item_name' => 'New Authors',
			'edit_item' => 'Edit Authors',
			'view_item' => 'View Authors',
			'update_item' => 'Update Authors',
			'search_items' => 'Search Authors',
			'parent_item' => 'Parent Authors',
			'parent_item_colon' => 'Parent Authors:'
		),
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'author')
	));

	register_taxonomy('mbt_genres', 'mbt_books', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => 'Genres',
			'singular_name' => 'Genre',
			'all_items' => 'All Genres',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Genres',
			'new_item_name' => 'New Genres',
			'edit_item' => 'Edit Genres',
			'view_item' => 'View Genres',
			'update_item' => 'Update Genres',
			'search_items' => 'Search Genres',
			'parent_item' => 'Parent Genres',
			'parent_item_colon' => 'Parent Genres:'
		),
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'genre')
	));

	register_taxonomy('mbt_themes', 'mbt_books', array(
		'hierarchical' => false,
		'labels' => array(
			'name' => 'Themes',
			'singular_name' => 'Theme',
			'all_items' => 'All Themes',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Themes',
			'new_item_name' => 'New Themes',
			'edit_item' => 'Edit Themes',
			'view_item' => 'View Themes',
			'update_item' => 'Update Themes',
			'search_items' => 'Search Themes',
			'parent_item' => 'Parent Themes',
			'parent_item_colon' => 'Parent Themes:'
		),
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'theme')
	));

	register_post_type('mbt_series_editor', array(
		'labels' => array(
			'name' => 'Series',
			'singular_name' => 'Series',
			'all_items' => 'All Series',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Series',
			'new_item_name' => 'New Series',
			'edit_item' => 'Edit Series',
			'view_item' => 'View Series',
			'update_item' => 'Update Series',
			'search_items' => 'Search Series',
			'parent_item' => 'Parent Series',
			'parent_item_colon' => 'Parent Series:'
		),
		'public' => true,
		'show_ui' => true,
		'query_var' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'exclude_from_search' => true,
		'has_archive' => false,
		'supports' => array('title', 'editor', 'thumbnail')
	));

	register_taxonomy('mbt_series', 'mbt_books', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => 'Series',
			'singular_name' => 'Series',
			'all_items' => 'All Series',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Series',
			'new_item_name' => 'New Series',
			'edit_item' => 'Edit Series',
			'view_item' => 'View Series',
			'update_item' => 'Update Series',
			'search_items' => 'Search Series',
			'parent_item' => 'Parent Series',
			'parent_item_colon' => 'Parent Series:'
		),
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'series')
	));
}

function mbt_add_series_to_menu() {
	add_submenu_page('edit.php?post_type=mbt_books', 'Series','Series', 'manage_options', 'edit.php?post_type=mbt_series_editor');
	remove_menu_page('edit.php?post_type=mbt_series_editor');
	remove_submenu_page('edit.php?post_type=mbt_books', 'edit-tags.php?taxonomy=mbt_series&amp;post_type=mbt_books');
}
add_action('admin_menu', 'mbt_add_series_to_menu', 5);

function mbt_get_series_post($series_term) {
	$query = new WP_Query(array("post_type" => "mbt_series_editor", "name" => $series_term));
	return $query->post;
}

function mbt_update_series($post_id)
{
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){return;}
	if(defined('DOING_AJAX') && DOING_AJAX){return;}

	$post = get_post($post_id);
	if($post->post_type != "mbt_series_editor"){return;}
	if(empty($post->post_name)){return;}

	$term_slug = get_post_meta($post_id, "term_slug", true);
	$term = get_term_by("slug", $term_slug, "mbt_series");
	if(empty($term) or $term->name != $post->post_title)
	{
		$term = term_exists($post->post_title, "mbt_series");
		if(!empty($term))
		{
			$term = get_term_by("id", $term['term_id'], "mbt_series");
			update_post_meta($post_id, "term_slug", $term->slug);
		}
		else
		{
			$term = wp_insert_term($post->post_title, "mbt_series", array('slug' => $post->post_name));
			if(!is_wp_error($term)) {
				$term = get_term_by("id", $term['term_id'], "mbt_series");
				update_post_meta($post_id, "term_slug", $term->slug);
			}
		}
	}
}
add_action('save_post', 'mbt_update_series'); 

function mbt_delete_series($post_id)
{
	$post = get_post($post_id);
	if($post->post_type != "mbt_series_editor"){return;}
	$term_slug = get_post_meta($post_id, "term_slug", true);
	$term = get_term_by("slug", $term_slug, "mbt_series");
	wp_delete_term($term->term_id, "mbt_series");
}
add_action('before_delete_post', 'mbt_delete_series');
