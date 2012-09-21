<?php

/*---------------------------------------------------------*/
/* Custom Post Types and Taxonomies                        */
/*---------------------------------------------------------*/

add_action('init', 'bt_create_post_types_and_taxonomies');
function bt_create_post_types_and_taxonomies()
{
	//create book categories
	register_taxonomy('product_category', 'min_products', array(
		'hierarchical' => true,
		'labels' => array(
			'name' => __('Book Categories', 'taxonomy general name'),
			'singular_name' => __( 'Book Category', 'taxonomy singular name'),
			'search_items' =>  __( 'Search Book Categories' ),
			'all_items' => __( 'All Book Categories' ),
			'parent_item' => __( 'Parent Book Category' ),
			'parent_item_colon' => __( 'Parent Book Category:' ),
			'edit_item' => __( 'Edit Book Category' ), 
			'update_item' => __( 'Update Book Category' ),
			'add_new_item' => __( 'Add New Book Category' ),
			'new_item_name' => __( 'New Book Category' ),
			'menu_name' => __( 'Book Category' ),
		),
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'product_category'),
	));

	//create book tags
	register_taxonomy('product_tag', 'min_products', array(
		'hierarchical' => false,
		'labels' => array(
			'name' =>  __( 'Book Tags', 'taxonomy general name' ),
			'singular_name' =>  __( 'Book Tag', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search Book Tags' ),
			'popular_items' => __( 'Popular Book Tag' ),
			'all_items' => __( 'All Book Tags' ),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __( 'Edit Book Tag' ), 
			'update_item' => __( 'Update Book Tag' ),
			'add_new_item' => __( 'Add New Book Tag' ),
			'new_item_name' => __( 'New Writer Book Tag' ),
			'separate_items_with_commas' => __( 'Separate Book Tags with commas' ),
			'add_or_remove_items' => __( 'Add or remove Book Tags' ),
			'choose_from_most_used' => __( 'Choose from the most used Book Tag' ),
			'menu_name' => __( 'Book Tags' ),
		),
		'show_ui' => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var' => true,
		'rewrite' => array('slug' => 'product_tag'),
	));

	//Add a taxonomy for use in creating book collections
	register_taxonomy('product_collection', 'min_products', array(
		'hierarchical' => true,
		'labels' => array(
			'name' =>  __( 'Book Collections', 'taxonomy general name' ),
			'singular_name' =>  __( 'Book Collection', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search Book Collections' ),
			'popular_items' => __( 'Popular Book Collection' ),
			'all_items' => __( 'All Book Collections' ),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __( 'Edit Book Collection' ), 
			'update_item' => __( 'Update Book Collection' ),
			'add_new_item' => __( 'Add New Book Collection' ),
			'new_item_name' => __( 'New Writer Book Collection' ),
			'separate_items_with_commas' => __( 'Separate Book Collections with commas' ),
			'add_or_remove_items' => __( 'Add or remove Book Collections' ),
			'choose_from_most_used' => __( 'Choose from the most used Book Collection' ),
			'menu_name' => __( 'Book Collections' ),
			),
		'show_ui' => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var' => true,
		'rewrite' => array('slug' => 'product_collection'),
	));

	//Custom Book Post Type
	register_post_type('min_products', array(
		'labels' => array(			
			'name' => _x('Books', 'post type general name'),
			'singular_name' => _x('Book', 'post type singular name'),
			'add_new' => _x('Add New', 'book'),
			'add_new_item' => __('Add New Book'),
			'edit_item' => __('Edit Book'),
			'new_item' => __('New Book'),
			'all_items' => __('All Books'),
			'view_item' => __('View Book'),
			'search_items' => __('Search Books'),
			'not_found' =>  __('No books found'),
			'not_found_in_trash' => __('No books found in Trash'), 
			'parent_item_colon' => '',
			'menu_name' => __('Books')
		),
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true, 
		'query_var' => true,
		'rewrite' => array('slug' => 'bookstore'),
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_icon' => plugins_url('images/option-icon-cart.png', dirname(__FILE__)),
		'menu_position' => 5, 
		'exclude_from_search' => false,
		'has_archive' => true,
		'supports' => array('title', 'editor', 'thumbnail'),
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