<?php

//Initialize library
add_action('init', 'bt_initialize_cmb_meta_boxes', 9999);
function bt_initialize_cmb_meta_boxes(){ if(!class_exists('cmb_Meta_Box')){ require_once('lib/metabox/init.php'); } }

//Add metaboxes
add_filter('cmb_meta_boxes', 'bt_add_metaboxes');
function bt_add_metaboxes( array $meta_boxes ) {
	global $bt_main_options, $_GET;
	
	// Start with an underscore to hide fields from custom fields list
	$paypalwarnings = '';

	if(empty($bt_main_options['paypal_email'])){
		$paypalwarnings .= '<div class="cmb_warning"> Paypal Email Address is Required for Paypal Buttons to Function. - 
		<a href="/wp-admin/edit.php?post_type=min_products&page=button-store-options">Set Email Address</a></div>';
	}

	// add warning for price.. Todo - make this work like normal validation
	$theprice = get_post_meta($_GET['post'], '_cmb_price', true);
	if(empty($theprice)){
		$paypalwarnings .= '<div class="cmb_warning"> Price is Required for Paypal Buttons to Function. - 
		Scroll up to add a price to this item.</div>';
	}
	$prefix = '_cmb_';
 
	$meta_boxes[] = array(
		'id'         => 'product_info',
		'title'      => 'Book Info',
		'pages'      => array( 'min_products', ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'fields'     => array(
			array(
				'name' => 'Book ID',
				'desc' => 'sku or unique ID',
				'id'   => $prefix . 'sku',
				'type' => 'text_small',
			),
			$bt_main_options['use_paypal'] == 'on' || $bt_main_options['use_kickstart'] == 'on' ?						
			array(
				'name' => 'Book Price',
				'desc' => 'Without the $ sign (Needed for Paypal - Helpful for Kickstart )',
				'id'   => $prefix . 'price',
				'type' => 'text_money',
			): NULL,
			$bt_main_options['use_paypal'] == 'on' || $bt_main_options['use_kickstart'] == 'on' ?						
			array(
				'name' => 'Book Sales Price',
				'desc' => 'Without the $ sign ( Helpful for Kickstart )',
				'id'   => $prefix . 'salesprice',
				'type' => 'text_money',
			): NULL,
			array(
				'name' => 'Author',
				'desc' => 'optional (for book or other published content)',
				'id'   => $prefix . 'author',
				'type' => 'text_small',
			),
			array(
				'name' => 'Additional Description',
				'desc' => 'short additional description below the add-to-cart button (optional)',
				'id'   => $prefix . 'additional_description',
				'type' => 'wysiwyg',
			),
			array(
				'name' => 'Featured Book',
				'desc' => 'Show in the featured books in the "Did You Miss.." sidebar widget ',
				'id'   => $prefix . 'featured',
				'type' => 'checkbox',
			),
			$bt_main_options['use_issuu'] == 'on' ?					
			array(
				'name' => 'Issuu Document ID',
				'desc' => 'the documentID of the Book for Issuu. This is a ridiculously long code like: 091013542540-7ccfb3de03724177a1d21fvdadc354a6',
				'id'   => $prefix . 'issue_doc_id',
				'type' => 'text',
			): NULL,
			$bt_main_options['use_amazon'] == 'on' ?					
			array(
				'name' => 'Amazon Button Code',
				'desc' => '(optional) paste in the button code for the Amazon Affiliate link for this item',
				'id'   => $prefix . 'button_code_amazon',
				'type' => 'textarea_code',
			) : NULL,
			$bt_main_options['use_cbd'] == 'on' ?						
			array(
				'name' => 'CBD Code',
				'desc' => '(optional) paste in the button code for the Christian Book Distributors link for this item',
				'id'   => $prefix . 'button_code_cbd',
				'type' => 'textarea_code',
			) : NULL,
			$bt_main_options['use_bnn'] == 'on' ?						
			array(
				'name' => 'Barnes & Noble Code',
				'desc' => '(optional) paste in the button code for the Barnes & Noble link for this item',
				'id'   => $prefix . 'button_code_bnn',
				'type' => 'textarea_code',
			) : NULL,
			$bt_main_options['use_ejunkie'] == 'on' ?						
			array(
				'name' => 'EJunkie Code',
				'desc' => '(optional) paste in the button code for the eJunkie link for this item',
				'id'   => $prefix . 'button_code_ejunkie',
				'type' => 'textarea_code',
			) : NULL,
			$bt_main_options['use_kickstart'] == 'on' ?			
			array(
				'name' => 'Kickstart Cart Code',
				'desc' => '(optional) paste in the button code for the Kickstart Cart link for this item',
				'id'   => $prefix . 'button_code_kickstart', 
				'type' => 'textarea_code',
			) : NULL,
			$bt_main_options['use_bnn_nook'] == 'on' ?						
			array(
				'name' => 'Nook Code',
				'desc' => '(optional) paste in the button code for the Nook link for this item',
				'id'   => $prefix . 'button_code_nook',
				'type' => 'textarea_code',
			) : NULL,
			$bt_main_options['use_kindle'] == 'on' ?						
			array(
				'name' => 'Kindle Code',
				'desc' => '(optional) paste in the button code for the Kindle link for this item',
				'id'   => $prefix . 'button_code_kindle',
				'type' => 'textarea_code',
			) : NULL,
			$bt_main_options['use_bim'] == 'on' ?						
			array(
				'name' => 'Books In Motion Code',
				'desc' => '(optional) paste in the button code for the Books In Motion link for this item',
				'id'   => $prefix . 'button_code_bim',
				'type' => 'textarea_code',
			) : NULL,
			$bt_main_options['use_sba'] == 'on' ?						
			array(
				'name' => 'Signed by Author Code',
				'desc' => '(optional) paste in the button code for the Signed by Author store link for this item',
				'id'   => $prefix . 'button_code_sba',
				'type' => 'textarea_code',
			) : NULL,

			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Paypal Book Options',
				'desc' => 'fill in the following if there are variations of this book - ie. hard cover and soft cover. Only for use with Paypal Buttons',
				'id'   => $prefix . 'paypal_options_title',
				'type' => 'title',
			) : NULL,
			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Add Paypal Button',
				'desc' => '(optional) Check box to enable Paypal Button. '.$paypalwarnings,
				'id'   => $prefix . 'button_code_paypal',
				'type' => 'checkbox',
			) : NULL,
			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Paypal Product Variations',
				'desc' => 'fill in the following if there are variations of this book - ie. hard cover and soft cover. Only for use with Paypal Buttons',
				'id'   => $prefix . 'paypal_variations_title',
				'type' => 'title',
			) : NULL,

			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Use Product Variations',
				'desc' => 'check the box to use multible book variations. If box not checked, make sure that the price (above) is set.',
				'id'   => $prefix . 'paypal_variations_toggle',
				'type' => 'checkbox',
			) : NULL,


			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Option Box Label',
				'desc' => 'If using book variations, this is the title of the variations selector. -- ie. Book Cover Type',
				'id'   => $prefix . 'paypal_variations_label',
				'type' => 'text_small',
			) : NULL,

			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Option 1 Name',
				'desc' => 'The Title of Variation 1. -- ie. soft cover',
				'id'   => $prefix . 'paypal_variation_name_1',
				'type' => 'text_small',
			) : NULL,

			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Option 1 Price',
				'desc' => 'The Price item with Variation 1 (decimal number only, no $ sign). -- ie. 10',
				'id'   => $prefix . 'paypal_variation_price_1',
				'type' => 'text_small',
			) : NULL,
			
			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => '',
				'desc' => '',
				'id'   => $prefix . 'paypal_variation_sep_1',
				'type' => 'Title',
			) : NULL,
			
			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Option 2 Name',
				'desc' => 'The Title of Variation 2. -- ie. hard cover',
				'id'   => $prefix . 'paypal_variation_name_2',
				'type' => 'text_small',
			) : NULL,

			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Option 2 Price',
				'desc' => 'The Price item with Variation 2 (decimal number only, no $ sign). -- ie. 12',
				'id'   => $prefix . 'paypal_variation_price_2',
				'type' => 'text_small',
			) : NULL,
			
			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => '',
				'desc' => '',
				'id'   => $prefix . 'paypal_variation_sep_2',
				'type' => 'Title',
			) : NULL,
			
			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Option 3 Name',
				'desc' => '(optional - leave blank to omit) The Title of Variation 3. -- ie. leather cover',
				'id'   => $prefix . 'paypal_variation_name_3',
				'type' => 'text_small',
			) : NULL,

			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Option 3 Price',
				'desc' => '(optional - leave blank to omit) The Price item with Variation 3 (decimal number only, no $ sign). -- ie. 14',
				'id'   => $prefix . 'paypal_variation_price_3',
				'type' => 'text_small',
			) : NULL,
			
			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => '',
				'desc' => '',
				'id'   => $prefix . 'paypal_variation_sep_3',
				'type' => 'Title',
			) : NULL,
			
			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Option 4 Name',
				'desc' => '(optional - leave blank to omit) The Title of Variation 4. -- ie. deluxe edition',
				'id'   => $prefix . 'paypal_variation_name_4',
				'type' => 'text_small',
			) : NULL,

			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Option 4 Price',
				'desc' => '(optional - leave blank to omit) The Price item with Variation 4 (decimal number only, no $ sign). -- ie. 16',
				'id'   => $prefix . 'paypal_variation_price_4',
				'type' => 'text_small',
			) : NULL,
			
			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => '',
				'desc' => '',
				'id'   => $prefix . 'paypal_variation_sep_4',
				'type' => 'Title',
			) : NULL,
			
			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Option 5 Name',
				'desc' => '(optional - leave blank to omit) The Title of Variation 5. -- ie. super deluxe edition with dvd',
				'id'   => $prefix . 'paypal_variation_name_5',
				'type' => 'text_small',
			) : NULL,

			$bt_main_options['use_paypal'] == 'on' ?						
			array(
				'name' => 'Option 5 Price',
				'desc' => '(optional - leave blank to omit) The Price item with Variation 5 (decimal number only, no $ sign). -- ie. 20',
				'id'   => $prefix . 'paypal_variation_price_5',
				'type' => 'text_small',
			) : NULL,
		),
	);
	return $meta_boxes;
}