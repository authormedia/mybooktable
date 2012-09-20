<?php
/*
Plugin Name: Book Table
Plugin URI: http://www.castlemediagroup.com
Description: A simple store plugin for books, allowing you to integrate with external shopping carts and bookstore websites.
Author: Jim Camomile	
Version: 1.2
Author URI: http://www.castlemediagroup.com

Dependancies: 

2. uses taxonomy images by Michael Fields for categories display function
3. uses custom fields using Bill Ericsons custom metabox library
4. uses custom admin pages by Tim Zook - based on custom fields 
*/


//include('lib/metabox/init.php'); // initiallize library
include('lib/metabox/boxes2.php'); // messing with the controls
// load main options from options panel
$main_options = get_option("button-store-options_options"); 
/*--------------------------------------------------*/
/* Custom Taxonomies - Product Categorie */
/*--------------------------------------------------*/

//hook into the init action and call create_book_taxonomies when it fires
add_action( 'init', 'create_product_category', 0 );

//create two taxonomies, genres and writers for the post type "book"
// Todo - add similar function to create a category listing widget and a layout for the main books page.
function create_product_category() 
{
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
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
  ); 	

  register_taxonomy('product_category',array('min_products'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'product_category' ),
  ));


  // Add new taxonomy, NOT hierarchical (like tags)
  $labels = array(
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
  ); 

  register_taxonomy('product_tag','min_products',array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array( 'slug' => 'product_tag' ),
  ));


// Add a taxonomy for use in creating book collections
  $labels = array(
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
  ); 

  register_taxonomy('product_collection','min_products',array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array( 'slug' => 'product_collection' ),
  ));
}


 
/*--------------------------------------------------*/
/* Custom Post Type - Books (Books Component) */
/*--------------------------------------------------*/
if ( ! function_exists( 'add_products' ) ) {
	function add_products() {
		// "Product" Custom Post Type
		$products_rewrite = 'bookstore'; 
		$args = array(
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
			'rewrite' => array( 'slug' => $products_rewrite ),
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_icon' => plugins_url('images/option-icon-cart.png',__FILE__),
			'menu_position' => 5, 
			'exclude_from_search' => false,
			'has_archive' => true, 
			'supports' => array( 'title','editor','thumbnail' ),
		);
				
	register_post_type( 'min_products', $args );
	}
}
add_action( 'init','add_products',20 );	

function show_product_label() {
	$obj = get_post_type_object('min_products');
	return $obj->labels->name;
}

// check to see if button code contains image tag
function find_img_tag($buttoncode) {
	$pattern = "/<img[^<]*>/i"; // detect the img tag to replace it with regex preg_replace
	$match = preg_match($pattern, $buttoncode);
	return $match;
}

// format for amazon text only code
function button_code_by_atag($buttoncode){ // experiment to parse html for the a tags instead of images
	$urlpattern = '/href[\'"]?([^\s\>\'"]*)[\'"\>]/'; // finds what is in an href tag
	$imagepattern = "/<img[^<]*>/i";
	$buttonimage = plugins_url('images/ejunkie_button2.png',__FILE__);
	$images = preg_match($imagepattern, $buttoncode, $imagematches);
	$newbutton = '<img src="'.$buttonimage.'" border="0" alt="Add to Cart"/>';
	$link = preg_match($urlpattern, $buttoncode, $thematches);
	$matchlink = $thematches[0];
	$output = '<a '.$matchlink.'">'.$newbutton.'</a>' ;
	if ($link) {
		print_r($imagematches);
	} else {
		return $buttoncode;
	}
}

// format to change button code
function change_button_image($buttoncode,$type){
	$pattern = "/<img[^<]*>/i"; // detect the img tag to replace it with regex preg_replace
	// use the second arg to load the correct button
	$thebutton = plugins_url('images/'.$type.'_button.png',__FILE__);
	$newbutton = '<img src="'.$thebutton.'" border="0" class="buttonstore-btn" alt="Add to Cart"/>';
	
	if (preg_replace($pattern,$newbutton,$buttoncode)){
		return preg_replace($pattern,$newbutton,$buttoncode,1);
	} else {
		return $buttoncode;
	}
}

// add button image to the link code
function add_button_image($buttoncode,$type){
	// use the second arg to load the correct button
	$thebutton = plugins_url('images/'.$type.'_button.png',__FILE__);
	$newbutton = '<img src="'.$thebutton.'" border="0" class="buttonstore-btn" alt="Add to Cart"/>';
	// check to see that this has a good chance of being a normal url
	if (substr($buttoncode,0,4)=='http'){ 
		// if good url, form it into a link tag and return it
		$linkbutton = '<a href="'.$buttoncode.'" target="_blank">'.$newbutton.'</a>';
		return $linkbutton;
	} else { 
		// just leave it as it is
		return $buttoncode;
	}
}

// filter content for product pages

// include widgets
require_once(WP_PLUGIN_DIR . "/" . basename(dirname(__FILE__)) . "/widgets.php");
// include content output
require_once(WP_PLUGIN_DIR . "/" . basename(dirname(__FILE__)) . "/content_output.php");


/*------------------------------------------*/
/* hide post-meta div or p and comments box for products in archives. Styles may need adjusting per theme    */
/*------------------------------------------*/
// 
function hide_product_meta() {
	if ( 'min_products' != get_post_type() ) {
		return; //$excerpt; //exit;
	}
   echo '<style type="text/css">
	.post-meta, .comments {display: none;}  
         </style>';
		 }

add_action('wp_head', 'hide_product_meta'); // hide some product settings


  /**
   * Add the CSS file to the page header
   *
   * @return void
   */
function enqueue_styles(){
	wp_enqueue_style('book-table-style', plugins_url('/css/book-table-style.css',__FILE__), array(), '1.1');
}
	add_action('wp_head', 'enqueue_styles'); // enqueue styles
	
  /**
   * Add list categories
   *
   */	
function list_store_categories(){
		global $taxonomy_images_plugin;  // THIS LIST USES TAXONOMY-IMAGES PLUGIN
	//list terms in a given taxonomy using wp_list_categories
	$type         = 'min_products';
	$taxonomy     = 'product_category';
	$orderby      = 'ID'; 
	$order        = 'DESC'; 
	$hierarchical = 0;      // 1 for yes, 0 for no
	$hide_empty   = 0;
	$pad_counts   = 0;
	
	$args = array(
	  'type'         => $type,
	  'taxonomy'     => $taxonomy,
	  'orderby'      => $orderby,
	  'order'        => $order,
	  'child_of'     => 0,
	  'parent'       => 0,
	  'hierarchical' => $hierarchical,
	  'hide_empty'   => $hide_empty,
      'pad_counts'   => $pad_counts
	);
	
	$output = '<div id="product-cats">';
	$thecats = get_categories($args);
/*	$thecats = apply_filters( 'taxonomy-images-get-terms', '', array(
		'orderby'     => $orderby,
		'taxonomy'     => $taxonomy
	 ));
	 */
	foreach($thecats as $mycat) { 
	$url = '/'.$mycat->taxonomy.'/'.$mycat->slug.'/';
	$output .= '<div class="prodcat">';
	$output .= '<a href="'.$url.'"><div class="image">';
//	$output .= wp_get_attachment_image( $mycat->image_id );
$img = $taxonomy_images_plugin->get_image_html( 'full', $mycat->term_id );
    $output .= $img;
	$output .= '</div><div class="name">';
	$output .= $mycat->name;
	$output .= '</a></div></div>';
	}
	$output .= '</div><div style="clear:both;"></div>';

	return $output;
}

function store_breadcrumbs() {
 	global $wp_query, $main_options;
	$theoption = $main_options['show_breadcrumbs'];
	
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
		echo $store_bc;
	// End Add Breadcrumbs
}

function load_breadcrumbs() {
	global $main_options; 
	if ($main_options['is_woo']=='on') { // if using woo, then hook breadcrumbs in the very nice woo_loop_before action hook.
		add_action('woo_loop_before', 'store_breadcrumbs'); // breadcrumbs
	} else { // settle to add breadcrumbs to a more native hook in WP, though it displays better in some templates than others
		add_action('get_template_part_content', 'store_breadcrumbs'); // breadcrumbs
	}
}
add_action('init', 'load_breadcrumbs'); // breadcrumbs

function cat_description(){
global $wp_query;
if (!is_archive()){
	return;
}
	// find the category 
	$term = get_term_by('slug', get_query_var('product_category'), 'product_category');
		if(empty($term->description)){
			return '<div class="no-cat-intro"></div>';
		}
	$catblurb = '<div class="cat-intro">';
	$catblurb .= $term->description;
	$catblurb .= '</div>';
	return $catblurb;
	
}
//add_filter('loop_start','cat_description');


// make a new archive for category pages
function get_store_cat_template( $archive_template ) {
	global $main_options; 
//	print_r($main_options);
	if ($main_options['use_templates']!='on') {
		return $archive_template;
	}

	global $wp_query;
//	$tax = get_query_var('taxonomy');
//     if ( $tax == 'product_category') {

// try approach that detects prod cats AND product index
		if (get_post_type() == 'min_products' && is_archive()) {
			// first check in the templatepath and stylesheet path, in case the templates were moved there
			$locatedtemplate = locate_template('store-cat-template.php');
			if ( !empty($locatedtemplate) ) {
			  $archive_template = locate_template( 'store-cat-template.php' ); // found in Theme, load it
			} else {
			  $archive_template = dirname( __FILE__ ) . '/templates/store-cat-template.php'; // load the one in the plugin
		}
     }
     return $archive_template; 
}
add_filter( 'archive_template', 'get_store_cat_template' );

/*
Other Functions
*/
//Utility functions
function string_limit_words($string, $word_limit)
{
	$words = explode(' ', $string, ($word_limit + 1));
	if(count($words) > $word_limit){ array_pop($words); }
	return implode(' ', $words);
}


// truncate by char count placing ellipses after nearest word.
function ttruncat($text,$numb) {
	if (strlen($text) > $numb) {
		$text = substr($text, 0, $numb);
		$text = substr($text,0,strrpos($text," "));
		$etc = "&hellip;";
		$text = $text.$etc;
	}
	return $text;
}


// Do custom admin pages
//Access the WordPress Pages via an Array
$load_pages = array();
$load_pages_obj = get_pages('sort_column=post_parent,menu_order'); 
		$load_pages[] = array(
			'name' => 'Select One',
			'value' => ''
			);
	foreach ($load_pages_obj as $load_page) {		
		$load_pages[] = array(
			'name' => $load_page->post_title,
			'value' => $load_page->post_name
			);
		}
//$load_pages_tmp = array_unshift($load_pages, "Select a page:");  

require_once('lib/custom-admin-pages/adminpages.php');

ap_add_theme_options_add_page("Book Table Options", "button-store-options", 
	array(
		array(
			'title' => 'General Options',
			'desc' => '',
			'id' => 'general_options',
			'settings_fields' => array(
				array(
					'name' => 'Use Woo Canvas Framework Features',
					'desc' => 'Check this only if you are using Woo Canvas Framework. If checked, this will enable some enhanced features only available for Woo Canvas Framework',
					'id'   => 'is_woo',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Use Custom Booktable Category and Single Page Templates',
					'desc' => 'Check this if you want to use the preconfigured templates for book categories and single book pages. Also, you can move these templates from the plugin subdirectory called templates to your theme for easier modification. If unchecked, you may need to apply some functionality, like category intros, to your theme files manually.',
					'id'   => 'use_templates',
					'type' => 'checkbox',
				),

				array(
					'name' => 'Show Breadcrumbs for Bookstore pages',
					'desc' => 'If checked, this displays breadcrumbs for the bookstore pages only. Not recommended if you are using something else to display breadcrumbs',
					'id'   => 'show_breadcrumbs',
					'type' => 'checkbox',
				),
				
			)
		),
		array( 
			'title' => 'Product Category Pages',
			'desc' => '',
			'id' => 'product_category_pages',
			'settings_fields' => array(
				
				
				
				array(
					'name' => 'Show Category Introductions',
					'desc' => 'If checked, Category Introductions will be shown at the top of each store category page. ',
					'id'   => 'show_cat_intros',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Show Store Buttons in Category Pages',
					'desc' => 'Check the box if you want the store buttons to show up in the product lists in category pages.',
					'id'   => 'buttons_in_archive',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Read More Button Label',
					'desc' => 'The "Read More" button text for links from category pages to the individual product pages. Default is "More Details" .',
					'id'   => 'readmorebutton',
					'type' => 'text',
				),
				array(
					'name' => 'Placement of Read More Links',
					'desc' => 'Where do you want the read more links to be for each product?" .',
					'id'   => 'readmore_buttons_placement',
					'type'    => 'radio_inline',
						'options' => array(
							array( 'name' => 'Above the Button Bar', 'value' => 'above', ),
							array( 'name' => 'Below the Button Bar', 'value' => 'below', ),
							array( 'name' => 'Don\'t show Read-More', 'value' => 'none', ),
					),
				),
				
				array(
					'name' => 'Text Above Button Bar',
					'desc' => 'Optional - the text that appears above the Buttons on each book page',
					'id'   => 'above_button_bar',
					'type' => 'text',
				),
				array(
					'name' => 'Text Below Button Bar',
					'desc' => 'Optional - the text that appears below the Buttons on each book page',
					'id'   => 'below_button_bar',
					'type' => 'text',
				),
				array(
					'name' => 'Number of Products per Page',
					'desc' => 'Choose the number of products to show per page on the main products page or category page. Default is 10.',
					'id'   => 'posts_per_page',
					'type' => 'text_small',
				),

				
				/*
				array(
					'name' => 'Use Issuu to show Book Excerpts',
					'desc' => 'Issuu is a really cool way to show a sneak preview of your book in a flipping book format. <a href="http://www.issuu.com/" target="_blank">Go to Issuu to sign up for a free account to get started.</a>',
					'id'   => 'use_issuu',
					'type' => 'checkbox',
				),
				*/
			)
		),
		array( 
			'title' => 'Related Products (Collections)',
			'desc' => 'You can display related products for each item by checking the activation checkbox and then setting the options. Products can be set to be related to each other by adding them to the same "collection" using the Collections box in the right sidebar of each product admin screen. You can also create new collections by clicking the link at the bottom of the collections box.',
			'id' => 'related_products',
			'settings_fields' => array(
				array(
					'name' => 'Show Related Products in Product Pages',
					'desc' => 'If Checked, the related products will display in product pages. Related products are created by selecting a Collection for each related product',
					'id'   => 'related_in_content',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Show Related Products in Excerpts',
					'desc' => 'If Checked, the related products will display in product excerpts in category pages. ',
					'id'   => 'related_in_excerpts',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Related Products Label',
					'desc' => 'Label to use for related products section',
					'id'   => 'related_label',
					'type' => 'text_small',
				),
				array(
					'name' => 'Show Image Thumbnails in Related Products',
					'desc' => '',
					'id'   => 'related_thumbnail',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Show Short Descriptions in Related Products',
					'desc' => 'show a brief excerpt of the product description in related products',
					'id'   => 'related_descrip',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Short Description length in Related Products',
					'desc' => 'length in characters - default is 120.',
					'id'   => 'related_descrip_len',
					'type' => 'text_small',
				),
			)
		),
		array(
			'title' => 'Services to Use',
			'desc' => 'Select the Services you are using from the options below. For each that you select you will see a link code box for that service in the admin page for each book. <br />Only books with a link code for that service will display the button for that service.',
			'id' => 'service_options',
			'settings_fields' => array(
				array(
					'name' => 'Amazon.com Affiliates',
					'desc' => '',
					'id'   => 'use_amazon',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Christian Book Distributors',
					'desc' => '',
					'id'   => 'use_cbd',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Barnes & Noble',
					'desc' => '',
					'id'   => 'use_bnn',
					'type' => 'checkbox',
				),
				array(
					'name' => 'EJunkie',
					'desc' => '',
					'id'   => 'use_ejunkie',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Kickstart Cart',
					'desc' => '',
					'id'   => 'use_kickstart',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Nook',
					'desc' => '(Barnes & Noble)',
					'id'   => 'use_bnn_nook',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Kindle',
					'desc' => '(Amazon.com)',
					'id'   => 'use_kindle',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Books in Motion',
					'desc' => '',
					'id'   => 'use_bim',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Signed by Author',
					'desc' => '',
					'id'   => 'use_sba',
					'type' => 'checkbox',
				),

				array(
					'name' => 'Paypal Standard Buttons',
					'desc' => '',
					'id'   => 'use_paypal',
					'type' => 'checkbox',
				),


			)
		),		
		array(
			'title' => 'Paypal Options',
			'desc' => 'These are settings you only need to bother if if using paypal buttons',
			'id' => 'paypal_options',
			'settings_fields' => array(
				array(
					'name' => 'Paypal Email Address',
					'desc' => 'needed only if you are using paypal standard shopping cart buttons',
					'id'   => 'paypal_email',
					'type' => 'text',
				),
				array(
					'name' => 'Paypal Thank You Page',
					'desc' => 'Choose a page on your website where you want paypal to return to after completing the sale. You might want to create a page for this purpose. If blank, the default is the books home page. This is needed only if you are using paypal standard shopping cart buttons',
					'id'   => 'paypal_thankyou_return',
					'type' => 'select',
					'options' => $load_pages,
				),
				array(
					'name' => 'Paypal Cancel Page',
					'desc' => 'Choose a page on your website where you want paypal to return to after a cancelled sale. You might want to create a page for this purpose.  If blank, the default is the books home page. This is needed only if you are using paypal standard shopping cart buttons',
					'id'   => 'paypal_cancel_return',
					'type' => 'select',
					'options' => $load_pages,

				),

			)
		),
	)
);

function catch_that_image() {
  global $post, $posts;
  $first_img = '';
  ob_start();
  ob_end_clean();
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
  $first_img = $matches [1] [0];

  if(empty($first_img)){ //Defines a default image
    $first_img = "/images/default.jpg";
  }
  return $first_img;
}

function castleimage($width,$height,$class='thumbnail') {
	if (function_exists('woo_image')){ // if woo themes woo_image is in play, by all means use it
		echo  woo_image('key=image&size=thumbnail&class='.$class.'&width='.$width.'&noheight=true');
	} elseif (has_post_thumbnail()) { // else if there is a featured image to grab, use that
		$default_attr = array(
			'class'	=> $class,
			'alt'	=> trim(strip_tags( $post->post_title )),
			'title'	=> trim(strip_tags( $post->post_title )),
		);
		the_post_thumbnail(array($width,$height),$default_attr); 
	//} elseif (function_exists('get_the_image')){ //- use  get-the-image but Justin Tadlock
	//	echo get_the_image();
	} else { // last straw, homegrown image finding script from cats that code.
		$theimage = catch_that_image($post->ID);
		$default_attr = array(
			'src'	=> $theimage,
			'class'	=> $class,
			'alt'	=> trim(strip_tags( $post->post_title )),
			'title'	=> trim(strip_tags( $post->post_title )),
		);
		the_post_thumbnail(array($width,$height),$default_attr); 
	}
}

function getcastleimage($width,$height,$class='thumbnail',$link='src') {
	global $post;
	if (function_exists('woo_image')){ // if woo themes woo_image is in play, by all means use it
		$output = woo_image('key=image&size=thumbnail&link='.$link.'&class='.$class.'&width='.$width.'&noheight=true&return=1');
	} elseif (has_post_thumbnail()) { // else if there is a featured image to grab, use that
		$default_attr = array(
			'class'	=> $class,
			'alt'	=> trim(strip_tags( $post->post_title )),
			'title'	=> trim(strip_tags( $post->post_title )),
		);
		$output = get_the_post_thumbnail($post->ID,array($width,$height),$default_attr); 
	} else { // last straw, homegrown image finding script from cats that code.
		$theimage = catch_that_image($post->ID);
		$default_attr = array(
			'src'	=> $theimage,
			'class'	=> $class,
			'alt'	=> trim(strip_tags( $post->post_title )),
			'title'	=> trim(strip_tags( $post->post_title )),
		);
		$output = get_the_post_thumbnail(array($width,$height),$default_attr); 
	}
	return $output;
}

/**
 * Display advanced TinyMCE editor in taxonomy page
 */

/*  
Borrowed the following from another plugin. Credits:

Taxonomy TinyMCE
Author: Jaime Martinez
Author URI: http://www.jaimemartinez.nl
*/


remove_filter( 'pre_term_description', 'wp_filter_kses' );
remove_filter( 'term_description', 'wp_kses_data' );
	
// add extra css to display quicktags correctly
add_action( 'admin_print_styles', 'taxonomy_tinycme_admin_head' );
function taxonomy_tinycme_admin_head() { ?>
	<style type="text/css">
		.quicktags-toolbar input{width: 55px !important;}
	</style> <?php
}

/*
 * Start replacing the description textarea on the edit detail page of a taxonomy (custom or 'category').
 * */

$show_description_column = TRUE;

add_filter('edit_tag_form_fields', 'taxonomy_tinycme_add_wp_editor_term');
add_filter('edit_category_form_fields', 'taxonomy_tinycme_add_wp_editor_term');
function taxonomy_tinycme_add_wp_editor_term($tag) { ?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="description"><?php _ex('Description', 'Taxonomy Description'); ?></label></th>
		<td>
			<?php  
				$settings = array(
					'wpautop' => true, 
					'media_buttons' => true, 
					'quicktags' => true, 
					'textarea_rows' => '15', 
					'textarea_name' => 'description' 
				);	
				wp_editor(html_entity_decode($tag->description ), 'description2', $settings); ?>	
		</td>	
	</tr> <?php
}

/*
 * Remove the default textarea from the edit detail page.
 * */
add_action('admin_head', 'taxonomy_tinycme_hide_description'); 
function taxonomy_tinycme_hide_description() {
	global $pagenow;
	//only hide on detail not yet on overview page.
	if( ($pagenow == 'edit-tags.php' && isset($_GET['action']) )) : 	
	?>
		<script type="text/javascript">
			jQuery(function($) {
				$('#description, textarea#tag-description').closest('.form-field').hide(); 
	 		}); 
 		</script>
 	<?php 
	endif; 
}

// lets hide the cat description from the category admin page if $show_description_column = FALSE
function manage_my_taxonomy_columns($columns)
{
	global $show_description_column;
	 // only edit the columns on the current taxonomy, this should be a setting.
	if ( $show_description_column)
	 	return $columns;

	// unset the description columns
	if ( $posts = $columns['description'] ){ unset($columns['description']); }
	 
	return $columns;
}
add_filter('manage_edit-post_tag_columns','manage_my_taxonomy_columns');
add_filter('manage_edit-category_columns','manage_my_taxonomy_columns');


function price_output($postID,$kickstart='',$paypal='') {
global $main_options;
	$rawprice = get_post_meta($postID, '_cmb_price', true);
	// strip out non-numerics and add decimals to price
	$price = preg_replace("/[^0-9,.]/", "", $rawprice);
	$price = number_format($price,2);
	$price = '$'.$price;
	// salesprice - used for kickstart only
	$rawsalesprice = get_post_meta($postID, '_cmb_salesprice', true);
	// strip out non-numerics and add decimals to price
	$salesprice = preg_replace("/[^0-9,.]/", "", $rawsalesprice);
	$salesprice = number_format($salesprice,2);
	$salesprice = '$'.$salesprice;

// if this is kickstart cart, we need to add the price and the sale price
	if( $kickstart=='on' && ( !empty($rawprice) || !empty($rawsalesprice))) {
		if ( !empty($rawprice) && !empty($rawsalesprice) ){
			$theprice .= '<div class="price"><span class="oldprice">'.$price.'</span> <span class="newprice">'.$salesprice.'</span></div>';
		} elseif (!empty($rawprice)) {
			$theprice .= '<div class="price">'.$price.'</div>'; // currently outputting price only for kickstart and paypal
		} else {
			$theprice .= '<div class="price">'.$salesprice.'</div>'; // currently outputting price only for kickstart and paypal
		}
	// if this is paypal and not variable, we need to output the normal price
	} elseif ( !empty($main_options['paypal_email']) && $paypal=='on' && $rawprice>0 ) {
	 	$theprice .= '<div class="price">'.$price.'</div>';
	}
	
	return $theprice;
}

		
// Related Products Function
function relatedproducts($postID) {
	global $main_options; 
	$related_label = (!empty($main_options['related_label']) ? $main_options['related_label'] : 'Related Products') ;

$has_thumbnail = $main_options['related_thumbnail']; // on or blank
$has_descrip = $main_options['related_descrip'];
$related_descrip_len = (!empty($main_options['related_descrip_len']) ? $main_options['related_descrip_len'] : 120) ; // set to selected length or 200 as default
	  
	//return 'the related books for ID-'.$postID;
	$collections = wp_get_post_terms($postID,'product_collection',$args);
	if ($collections){
		$output = '';
		$output .= '<div class="relatedproducts">';
		$output .= '<h3>'.$related_label.'</h3>';
		$numcollections = count($collections);
	    // print_r($numcollections);
		foreach ($collections as $collection) {
			$name = $collection->name;
			$slug = $collection->slug;
			if ($numcollections > 1){
			$output .= '<div class="name">'.$name.'</div>';
			}
				$relatedbooks = new WP_Query( array( 'product_collection' => $slug, 'post__not_in' => array($postID) ) );
					while ($relatedbooks->have_posts()) : $relatedbooks->the_post();
						$output .= '<div class="product">';
						if( !is_archive() && $has_thumbnail=='on'){ 
							$image = getcastleimage(100,150,'thumbnail','img');
							$output .= '<div class="image"><a href="'.get_permalink().'">'.$image.'</a></div>';
			   			}
						$output .= '<div class="link"><a href="'.get_permalink().'">'.get_the_title().'</a></div>';	
						if($has_descrip=='on'){ 
							$excerpt = strip_tags(get_the_content());
							$excerpt = ttruncat($excerpt, $related_descrip_len);
							$output .= '<div class="description">'.$excerpt.'</div>';	
						}
						$output .= price_output($post->ID,$main_options['use_kickstart'],$main_options['use_paypal']);
						$output .= '<div class="pbutton">'.buttoncode(get_the_ID()).'</div>';
						$output .= '<div class="clearme"></div>';
						$output .= '</div>';
	
					endwhile;
					// Reset Post Data
					wp_reset_postdata();			
		} // end foreach 
		$output .= '</div>';
	} // if collections
	return $output;
}

// change the number of posts per page for the product archives
function set_product_number( $query ) {
	global $main_options; 
	$postsper = (!empty($main_options['posts_per_page']) ? $main_options['posts_per_page'] : 10) ; // if no option is set then default is 10
	if ( 'min_products' != get_post_type() && $query->is_main_query() ) { // is only for min_product archives and only on main query
       $query->query_vars['posts_per_page'] = $postsper;
    }
}
add_action( 'pre_get_posts', 'set_product_number' );

// change the excerpt more ... This is used only for the sake of the template but it is generally a good idea
function new_excerpt_more( $more ) {
	return ' &hellip;';
}
add_filter('excerpt_more', 'new_excerpt_more');
?>
