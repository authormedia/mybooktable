<?php

/*---------------------------------------------------------*/
/* Cusom Taxonomies                                        */
/*---------------------------------------------------------*/

function mbt_init_taxonomies() {
	add_action('init', 'mbt_create_taxonomies');
	add_filter('parent_file', 'mbt_override_taxonomy_parent_files');
	add_action('admin_init', 'mbt_init_taxonomy_editors');
}
add_action('mbt_init', 'mbt_init_taxonomies');

function mbt_create_taxonomies()
{
	register_taxonomy('mbt_author', 'mbt_book', array(
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
		'rewrite' => array('slug' => apply_filters('mbt_author_rewrite_name', 'authors'))
	));

	register_taxonomy('mbt_genre', 'mbt_book', array(
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
		'rewrite' => array('slug' => apply_filters('mbt_genre_rewrite_name', 'genre'))
	));

	register_taxonomy('mbt_series', 'mbt_book', array(
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
		'rewrite' => array('slug' => apply_filters('mbt_series_rewrite_name', 'series'))
	));
}

function mbt_override_taxonomy_parent_files() {
	global $pagenow, $parent_file, $submenu_file;

	if($pagenow == "edit-tags.php" and ($_GET['taxonomy'] == "mbt_series" or $_GET['taxonomy'] == "mbt_genre" or $_GET['taxonomy'] == "mbt_author")) {
		$parent_file = "mbt_dashboard";
	}

	return $parent_file;
}


/*---------------------------------------------------------*/
/* Custom Images for Taxonomies                            */
/*---------------------------------------------------------*/

function mbt_init_taxonomy_editors() {
	add_action('in_admin_header', 'mbt_taxonomy_image_add_screen_post_type');

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
}

function mbt_taxonomy_image_add_screen_post_type() {
	global $current_screen, $taxonomy;
	if(isset($_REQUEST['taxonomy']) and ($_REQUEST['taxonomy'] == 'mbt_author' or $_REQUEST['taxonomy'] == 'mbt_genre' or $_REQUEST['taxonomy'] == 'mbt_series')) {
		$current_screen->post_type = "mbt_book";
	}
}

function mbt_add_taxonomy_image_edit_form() {
?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="mbt_tax_image_url">Image</label></th>
		<td>
			<input type="text" id="mbt_tax_image_url" name="mbt_tax_image_url" value="<?php echo(mbt_get_taxonomy_image($_REQUEST['taxonomy'], $_REQUEST['tag_ID'])); ?>" />
    		<input id="mbt_upload_tax_image_button" type="button" class="button" value="Upload" />
        </td>
	</tr>
<?php
}

function mbt_add_taxonomy_image_add_form() {
?>
	<div class="form-field">
		<label for="mbt_tax_image_url">Image</label>
		<input type="text" id="mbt_tax_image_url" name="mbt_tax_image_url" value="" />
		<input id="mbt_upload_tax_image_button" type="button" class="button" value="Upload" />
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