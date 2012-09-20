<?php

//Include the code
require_once(get_stylesheet_directory().'/lib/custom-admin-pages/adminpages.php');

//Create the admin page
ap_add_theme_options_add_page("Test Admin Page", "ap-test-options-page", 
	array(
		array(
			'title' => 'Test Settings Section',
			'desc' => 'There are many useful settings here.',
			'id' => 'test_settings',
			'settings_fields' => array(
				array(
					'name' => 'Display Info',
					'desc' => 'Show The Info',
					'id' => 'show_info',
					'type' => 'checkbox'
				),
				array(
					'name' => 'Quote Blurb',
					'desc' => 'Add an quote blurb to this page',
					'id' => 'blurb',
					'type' => 'text'
				)
			)
		)
	)
);

