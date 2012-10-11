<?php

//Include the library
require_once('lib/custom-admin-pages/custom-admin-pages.php');

//Add the options
cap_add_submenu_page("edit.php?post_type=bt_products", "Book Table Options", "button-store-options", 
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
					'desc' => 'If checked, this displays breadcrumbs for the bookstore pages only. Not recommended if you are using something else to display breadcrumbs.',
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
					'desc' => 'The "Read More" button text for links from category pages to the individual product pages.',
					'id'   => 'readmorebutton',
					'type' => 'text',
					'default' => 'More Details'
				),
				array(
					'name' => 'Placement of Read More Links',
					'desc' => 'Where do you want the read more links to be for each product?',
					'id'   => 'readmore_buttons_placement',
					'type'    => 'radio_inline',
						'options' => array(
							array( 'name' => 'Above the Button Bar', 'value' => 'above', ),
							array( 'name' => 'Below the Button Bar', 'value' => 'below', ),
							array( 'name' => 'Don\'t show Read-More', 'value' => '', ),
					)
				),
				array(
					'name' => 'Number of Products per Page',
					'desc' => 'Choose the number of products to show per page on the main products page or category page.',
					'id'   => 'posts_per_page',
					'type' => 'text_small',
					'default' => 10
				),
				array(
					'name' => 'Show Buy Buttons in Excerpts',
					'desc' => 'If Checked, the buy buttons will display in product excerpts in category pages.',
					'id'   => 'buttons_in_excerpt',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Use Issuu to show Book Excerpts',
					'desc' => 'Issuu is a useful tool for showing a sneak preview of your book in a flip book format. <a href="http://www.issuu.com/" target="_blank">Go to Issuu to sign up for a free account to get started.</a>',
					'id'   => 'use_issuu',
					'type' => 'checkbox',
				)
			)
		),
		array( 
			'title' => 'Book Collections',
			'desc' => 'You can group related books into collections by using the "Book Collections" box in the right sidebar of the edit product screen. You can also create new collections by clicking the link at the bottom of the box.',
			'id' => 'book_collections',
			'settings_fields' => array(
				array(
					'name' => 'Show Book Collections in Product Pages',
					'desc' => 'If Checked, the related products will display in product pages. Related products are created by selecting a Collection for each related product',
					'id'   => 'related_in_content',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Show Book Collections in Excerpts',
					'desc' => 'If Checked, the related products will display in product excerpts in category pages.',
					'id'   => 'related_in_excerpts',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Show Image Thumbnails in Book Collections',
					'desc' => '',
					'id'   => 'related_thumbnail',
					'type' => 'checkbox',
				),
				array(
					'name' => 'Short Description in Book Collections',
					'desc' => 'Length in characters of description, set to 0 to disable.',
					'id'   => 'related_descrip_len',
					'type' => 'text_small',
					'default' => 120
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
					'id'   => 'use_nook',
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
			'desc' => 'These settings are only relevant if you are using paypal buttons.',
			'id' => 'paypal_options',
			'settings_fields' => array(
				array(
					'name' => 'Paypal Email Address',
					'desc' => 'Needed only if you are using paypal standard shopping cart buttons.',
					'id'   => 'paypal_email',
					'type' => 'text',
				),
				array(
					'name' => 'Paypal Thank You Page',
					'desc' => 'Choose a page on your website where you want paypal to return to after completing the sale. This is optional.',
					'id'   => 'paypal_thankyou_return',
					'type' => 'page',
				),
				array(
					'name' => 'Paypal Cancel Page',
					'desc' => 'Choose a page on your website where you want paypal to return to after a cancelled sale. This is optional.',
					'id'   => 'paypal_cancel_return',
					'type' => 'page',
				)
			)
		),
	)
);
