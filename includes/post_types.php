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
			'name' => __('Books', 'mybooktable'),
			'singular_name' => __('Book', 'mybooktable'),
			'all_items' => __('Books', 'mybooktable'),
			'add_new' => __('Add New Book', 'mybooktable'),
			'add_new_item' => __('Add New Book', 'mybooktable'),
			'new_item_name' => __('New Book', 'mybooktable'),
			'edit_item' => __('Edit Book', 'mybooktable'),
			'view_item' => __('View Book', 'mybooktable'),
			'update_item' => __('Update Book', 'mybooktable'),
			'search_items' => __('Search Books', 'mybooktable'),
			'parent_item' => __('Parent Book', 'mybooktable'),
			'parent_item_colon' => __('Parent Book:', 'mybooktable'),
		),
		'public' => true,
		'show_ui' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => 5,
		'exclude_from_search' => false,
		'has_archive' => true,
		'supports' => array('title'),
		'rewrite' => array('slug' => mbt_get_product_slug(), 'with_front' => false)
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

/*---------------------------------------------------------*/
/* Post Manager Columns                                    */
/*---------------------------------------------------------*/

add_filter('manage_mbt_book_posts_columns', 'mbt_modify_post_manager_columns');

function mbt_modify_post_manager_columns($columns) {
	unset($columns['date']);
	return $columns;
}
