<?php

//Include the code
require_once('lib/custom-admin-pages/custom-admin-pages.php');

//Create the admin page
cap_add_menu_page("Test Admin Page", "test_admin_page", 
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

