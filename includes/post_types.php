<?php

/*---------------------------------------------------------*/
/* Custom Post Types and Taxonomies                        */
/*---------------------------------------------------------*/

add_action('init', 'bt_create_post_types_and_taxonomies');
function bt_create_post_types_and_taxonomies()
{
	//add custom product post type
	register_post_type('bt_products', array(
		'labels' => array(			
			'name' => 'Books',
			'singular_name' => 'Book',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Book',
			'edit_item' => 'Edit Book',
			'new_item' => 'New Book',
			'all_items' => 'All Books',
			'view_item' => 'View Book',
			'search_items' => 'Search Books',
			'not_found' =>  'No books found',
			'not_found_in_trash' => 'No books found in Trash', 
			'parent_item_colon' => '',
			'menu_name' => 'Books'
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
		'supports' => array('title', 'editor', 'thumbnail'),
		'rewrite' => array('slug' => 'products'),
	));

	//create categories
	register_taxonomy('bt_product_category', 'bt_products', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => 'Book Categories',
			'singular_name' => 'Book Category',
			'search_items' => 'Search Book Categories',
			'all_items' => 'All Book Categories',
			'parent_item' => 'Parent Book Category',
			'parent_item_colon' => 'Parent Book Category:',
			'edit_item' => 'Edit Book Category',
			'view_item' => 'View Book Category',
			'update_item' => 'Update Book Category',
			'add_new_item' => 'Add New Book Category',
			'new_item_name' => 'New Book Category'
		),
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'product_category'),
	));

	//create tags
	register_taxonomy('bt_product_tag', 'bt_products', array(
		'hierarchical' => false,
		'labels' => array(
			'name' => 'Book Tags',
			'singular_name' => 'Book Tag',
			'search_items' => 'Search Book Tags',
			'all_items' => 'All Book Tags',
			'edit_item' => 'Edit Book Tag',
			'view_item' => 'View Book Tag',
			'update_item' => 'Update Book Tag',
			'add_new_item' => 'Add New Book Tag',
			'new_item_name' => 'New Book Tag',
			'separate_items_with_commas' => 'Separate Book Tags with commas',
			'add_or_remove_items' => 'Add or remove Book Tags',
			'choose_from_most_used' => 'Choose from the most used Book Tag'
		),
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'product_tag'),
	));

	//create collections
	register_taxonomy('bt_product_collection', 'bt_products', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => 'Book Collections',
			'singular_name' => 'Book Collection',
			'search_items' => 'Search Book Collections',
			'all_items' => 'All Book Collections',
			'parent_item' => 'Parent Book Collection',
			'parent_item_colon' => 'Parent Book Collection:',
			'edit_item' => 'Edit Book Collection',
			'view_item' => 'View Book Collection',
			'update_item' => 'Update Book Collection',
			'add_new_item' => 'Add New Book Collection',
			'new_item_name' => 'New Book Collection'
		),
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'product_collection'),
	));
}



/*-------------------------------------------------------------------------------------------------*/
/* Replace Taxonomy Description Editor (Borrowed from "Taxonomy TinyMCE" plugin by Jaime Martinez) */
/*-------------------------------------------------------------------------------------------------*/

// add extra css to display quicktags correctly
add_action('admin_print_styles', 'taxonomy_tinycme_admin_head');
function taxonomy_tinycme_admin_head() {
	?>
		<style type="text/css">
			.quicktags-toolbar input{width: auto !important;}
		</style>
	<?php
}

//Remove the default textarea from the edit detail page of taxonomies
add_action('admin_head', 'taxonomy_tinycme_hide_description'); 
function taxonomy_tinycme_hide_description() {
	global $pagenow;
	//only hide on detail not yet on overview page.
	if($pagenow == 'edit-tags.php' and isset($_GET['action'])){	
		?>
			<script type="text/javascript">
				jQuery(function($) {
					$('#description, textarea#tag-description').closest('.form-field').hide(); 
				}); 
			</script>
		<?php 
	}
}

//Replace the description textarea on the edit detail page of taxonomies
add_filter('edit_tag_form_fields', 'taxonomy_tinycme_add_wp_editor_term');
add_filter('edit_category_form_fields', 'taxonomy_tinycme_add_wp_editor_term');
function taxonomy_tinycme_add_wp_editor_term($tag) {
	?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="description"><?php _ex('Description', 'Taxonomy Description'); ?></label></th>
			<td><?php wp_editor(html_entity_decode($tag->description ), 'description_editor', array('wpautop' => true, 'media_buttons' => true, 'quicktags' => true, 'textarea_rows' => '15', 'textarea_name' => 'description')); ?></td>
		</tr>
	<?php
}