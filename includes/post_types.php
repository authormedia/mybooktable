<?php

/*---------------------------------------------------------*/
/* Custom Post Types                                       */
/*---------------------------------------------------------*/

function mbt_post_types_init() {
	add_action('init', 'mbt_register_post_types');
	add_filter('parent_file', 'mbt_override_post_types_parent_files');
	add_filter('post_updated_messages', 'mbt_override_post_updated_messages');
}
add_action('mbt_init', 'mbt_post_types_init');

function mbt_register_post_types()
{
	register_post_type('mbt_book', array(
		'labels' => array(
			'name' => 'Books',
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
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => 5,
		'exclude_from_search' => false,
		'has_archive' => true,
		'supports' => array('title'),
		'rewrite' => array('slug' => apply_filters('mbt_book_rewrite_name', 'books'))
	));
}

function mbt_override_post_types_parent_files() {
	global $pagenow, $parent_file, $submenu_file;

	if(($pagenow == "post.php" or $pagenow == "post-new.php") and get_post_type() == "mbt_book") {
		$parent_file = "mbt_dashboard";
		$submenu_file = "edit.php?post_type=mbt_book";
	}

	return $parent_file;
}

function mbt_override_post_updated_messages($messages) {
	$post_id = isset($_GET['post']) ? intval($_GET['post']) : (isset($_REQUEST['post_ID']) ? intval($_REQUEST['post_ID']) : 0);
	if(get_post_type($post_id) == "mbt_book") {
		$messages['post'][1] = sprintf('Book updated. <a href="%s">View book</a>', esc_url(get_permalink($post_id)));
	}
	return $messages;
}
