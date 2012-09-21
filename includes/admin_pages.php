<?php

require_once('lib/custom-admin-pages/adminpages.php');

//Access the WordPress Pages via an Array for page selector
$load_pages_obj = get_pages('sort_column=post_parent,menu_order'); 
$load_pages[] = array('name' => 'Select One','value' => '');
foreach ($load_pages_obj as $load_page) {		
	$load_pages[] = array('name' => $load_page->post_title, 'value' => $load_page->post_name);
}

//Add the options
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
				)
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
				)
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
				)
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
				)
			)
		),
	)
);
