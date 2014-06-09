<?php

/*---------------------------------------------------------*/
/* Cusom Taxonomies                                        */
/*---------------------------------------------------------*/

function mbt_taxonomies_init() {
	add_action('init', 'mbt_register_taxonomies');
	add_filter('parent_file', 'mbt_override_taxonomy_parent_files');
	add_action('admin_init', 'mbt_taxonomy_editors_init');
}
add_action('mbt_init', 'mbt_taxonomies_init');

function mbt_register_taxonomies()
{
	register_taxonomy('mbt_author', 'mbt_book', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => __('Authors', 'mybooktable'),
			'singular_name' => __('Author', 'mybooktable'),
			'all_items' => __('All Authors', 'mybooktable'),
			'add_new' => __('Add New', 'mybooktable'),
			'add_new_item' => __('Add New Author', 'mybooktable'),
			'new_item_name' => __('New Author', 'mybooktable'),
			'edit_item' => __('Edit Author', 'mybooktable'),
			'view_item' => __('View Authors', 'mybooktable'),
			'update_item' => __('Update Author', 'mybooktable'),
			'search_items' => __('Search Authors', 'mybooktable'),
			'parent_item' => __('Parent Author', 'mybooktable'),
			'parent_item_colon' => __('Parent Authors:', 'mybooktable'),
		),
		'show_ui' => true,
		'rewrite' => array('slug' => apply_filters('mbt_author_rewrite_name', _x('authors', 'URL slug', 'mybooktable')))
	));

	register_taxonomy('mbt_genre', 'mbt_book', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => __('Genres', 'mybooktable'),
			'singular_name' => __('Genre', 'mybooktable'),
			'all_items' => __('All Genres', 'mybooktable'),
			'add_new' => __('Add New', 'mybooktable'),
			'add_new_item' => __('Add New Genre', 'mybooktable'),
			'new_item_name' => __('New Genre', 'mybooktable'),
			'edit_item' => __('Edit Genre', 'mybooktable'),
			'view_item' => __('View Genres', 'mybooktable'),
			'update_item' => __('Update Genre', 'mybooktable'),
			'search_items' => __('Search Genres', 'mybooktable'),
			'parent_item' => __('Parent Genre', 'mybooktable'),
			'parent_item_colon' => __('Parent Genres:', 'mybooktable'),
		),
		'show_ui' => true,
		'rewrite' => array('slug' => apply_filters('mbt_genre_rewrite_name', _x('genre', 'URL slug', 'mybooktable')))
	));

	register_taxonomy('mbt_series', 'mbt_book', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => __('Series', 'mybooktable'),
			'singular_name' => __('Series', 'mybooktable'),
			'all_items' => __('All Series', 'mybooktable'),
			'add_new' => __('Add New', 'mybooktable'),
			'add_new_item' => __('Add New Series', 'mybooktable'),
			'new_item_name' => __('New Series', 'mybooktable'),
			'edit_item' => __('Edit Series', 'mybooktable'),
			'view_item' => __('View Series', 'mybooktable'),
			'update_item' => __('Update Series', 'mybooktable'),
			'search_items' => __('Search Series', 'mybooktable'),
			'parent_item' => __('Parent Series', 'mybooktable'),
			'parent_item_colon' => __('Parent Series:', 'mybooktable'),
		),
		'show_ui' => true,
		'rewrite' => array('slug' => apply_filters('mbt_series_rewrite_name', _x('series', 'URL slug', 'mybooktable')))
	));

	register_taxonomy('mbt_tag', 'mbt_book', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => __('Tags', 'mybooktable'),
			'singular_name' => __('Tag', 'mybooktable'),
			'all_items' => __('All Tags', 'mybooktable'),
			'add_new' => __('Add New', 'mybooktable'),
			'add_new_item' => __('Add New Tag', 'mybooktable'),
			'new_item_name' => __('New Tag', 'mybooktable'),
			'edit_item' => __('Edit Tag', 'mybooktable'),
			'view_item' => __('View Tags', 'mybooktable'),
			'update_item' => __('Update Tag', 'mybooktable'),
			'search_items' => __('Search Tags', 'mybooktable'),
			'parent_item' => __('Parent Tag', 'mybooktable'),
			'parent_item_colon' => __('Parent Tags:', 'mybooktable'),
		),
		'show_ui' => true,
		'rewrite' => array('slug' => apply_filters('mbt_tag_rewrite_name', mbt_get_product_slug()._x('tag', 'URL slug', 'mybooktable')))
	));
}

function mbt_override_taxonomy_parent_files() {
	global $pagenow, $parent_file, $submenu_file;

	if($pagenow == "edit-tags.php" and ($_GET['taxonomy'] == "mbt_series" or $_GET['taxonomy'] == "mbt_genre" or $_GET['taxonomy'] == "mbt_author" or $_GET['taxonomy'] == "mbt_tag")) {
		$parent_file = "mbt_dashboard";
	}

	return $parent_file;
}



/*---------------------------------------------------------*/
/* Custom Images for Taxonomies                            */
/*---------------------------------------------------------*/

function mbt_taxonomy_editors_init() {
	add_filter('mbt_author_edit_form_fields', 'mbt_add_taxonomy_image_edit_form');
	add_filter('mbt_author_add_form_fields', 'mbt_add_taxonomy_image_add_form');
	add_action('edited_mbt_author', 'mbt_save_taxonomy_image_edit_form');
	add_action('created_mbt_author', 'mbt_save_taxonomy_image_add_form');

	add_filter('mbt_genre_edit_form_fields', 'mbt_add_taxonomy_image_edit_form');
	add_filter('mbt_genre_add_form_fields', 'mbt_add_taxonomy_image_add_form');
	add_action('edited_mbt_genre', 'mbt_save_taxonomy_image_edit_form');
	add_action('created_mbt_genre', 'mbt_save_taxonomy_image_add_form');

	add_filter('mbt_series_edit_form_fields', 'mbt_add_taxonomy_image_edit_form');
	add_filter('mbt_series_add_form_fields', 'mbt_add_taxonomy_image_add_form');
	add_action('edited_mbt_series', 'mbt_save_taxonomy_image_edit_form');
	add_action('created_mbt_series', 'mbt_save_taxonomy_image_add_form');

	add_filter('mbt_tag_edit_form_fields', 'mbt_add_taxonomy_image_edit_form');
	add_filter('mbt_tag_add_form_fields', 'mbt_add_taxonomy_image_add_form');
	add_action('edited_mbt_tag', 'mbt_save_taxonomy_image_edit_form');
	add_action('created_mbt_tag', 'mbt_save_taxonomy_image_add_form');
}

function mbt_add_taxonomy_image_edit_form() {
?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="mbt_tax_image_url"><?php _e('Image', 'mybooktable') ?></label></th>
		<td>
			<input type="text" id="mbt_tax_image_url" name="mbt_tax_image_url" value="<?php echo(mbt_get_taxonomy_image($_REQUEST['taxonomy'], $_REQUEST['tag_ID'])); ?>" />
			<input id="mbt_upload_tax_image_button" type="button" class="button" value="<?php _e('Upload', 'mybooktable'); ?>" />
		</td>
	</tr>
<?php
}

function mbt_add_taxonomy_image_add_form() {
?>
	<div class="form-field">
		<label for="mbt_tax_image_url"><?php _e('Image', 'mybooktable') ?></label>
		<input type="text" id="mbt_tax_image_url" name="mbt_tax_image_url" value="" />
		<input id="mbt_upload_tax_image_button" type="button" class="button" value="<?php _e('Upload', 'mybooktable'); ?>" />
	</div>
<?php
}

function mbt_save_taxonomy_image_edit_form() {
	if(!empty($_REQUEST['taxonomy']) and !empty($_REQUEST['tag_ID']) and !empty($_REQUEST['mbt_tax_image_url'])) {
		mbt_save_taxonomy_image($_REQUEST['taxonomy'], $_REQUEST['tag_ID'], $_REQUEST['mbt_tax_image_url']);
	}
}

function mbt_save_taxonomy_image_add_form($term_id) {
	if(!empty($_REQUEST['taxonomy']) and !empty($_REQUEST['mbt_tax_image_url'])) {
		mbt_save_taxonomy_image($_REQUEST['taxonomy'], $term_id, $_REQUEST['mbt_tax_image_url']);
	}
}
