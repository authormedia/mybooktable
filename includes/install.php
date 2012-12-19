<?php

/*---------------------------------------------------------*/
/* Installation Functions                                  */
/*---------------------------------------------------------*/

function mbt_install() {
	mbt_install_pages();
	mbt_install_examples();
}

function mbt_install_pages() {
	global $mbt_main_settings;
	if($mbt_main_settings['bookstore_page'] <= 0) {
		$mbt_main_settings['bookstore_page'] = wp_insert_post(array(
			'post_title' => 'Bookstore',
			'post_content' => '[mbt_bookstore]',
			'post_status' => 'publish',
			'post_type' => 'page'
		));
		update_option("mbt_main_settings", $mbt_main_settings);
	}
}

function mbt_install_examples() {
	global $mbt_main_settings;
	if(!$mbt_main_settings['installed_examples']) {
		
		//Create Genre
		$fantasy = wp_insert_term('Fantasy', 'mbt_genres', array('slug' => 'fantasy'));

		//Create Themes
		$journey = wp_insert_term('Journey', 'mbt_themes', array('slug' => 'journey'));

		//Create Series
		wp_insert_post(array(
			'post_title' => 'The Lord of the Rings',
			'post_name' => 'lordoftherings',
			'post_content' => 'This is a cool series.',
			'post_status' => 'publish',
			'post_type' => 'mbt_series_editor'
		));
		$lordoftherings = get_term_by("name", 'The Lord of the Rings', "mbt_series", ARRAY_A);
		//$lordoftherings = wp_insert_term('The Lord of the Rings', 'mbt_series', array('slug' => 'lordoftherings'));

		//Create Author
		$tolkien = wp_insert_term('J. R. R. Tolkien', 'mbt_authors', array('slug' => 'tolkien'));

		//Create Books
		$post_id = wp_insert_post(array(
			'post_title' => 'The Fellowship of the Ring',
			'post_content' => '
Book I: The Ring Sets Out: 
The first chapter in the book begins in a light vein, following the tone of The Hobbit. Bilbo Baggins celebrates his 111th (or eleventy-first, as it 
is called in Hobbiton) birthday on the same day, September 22, that his relative and adopted heir Frodo Baggins celebrates his coming of age 
at 33. At the birthday party, Bilbo departs from the Shire, the land of the Hobbits, for what he calls a permanent holiday. He leaves Frodo 
his remaining belongings, including his home, Bag End, and (after some persuasion by the wizard Gandalf) the Ring he had found on his adventures 
(which he used to make himself invisible). Gandalf leaves on his own business, warning Frodo to keep the Ring secret. 
Over the next 17 years Gandalf periodically pays short visits to Bag End. One spring night, he arrives to warn Frodo about the truth of Bilbo\'s ring; 
it is the One Ring of Sauron the Dark Lord. Sauron forged it to subdue and rule Middle-earth, but in the War of the Last Alliance, he was defeated by 
Gil-galad the Elven King and Elendil, High King of Arnor and Gondor, though they themselves perished in the deed. Isildur, Elendil\'s son, cut the 
Ring from Sauron\'s finger. Sauron was thus overthrown, but the Ring itself was not destroyed as Isildur kept it for himself. Isildur was slain soon 
afterwards in the Battle of the Gladden Fields, and the Ring was lost in Great River Anduin. Thousands of years later, it was found by the hobbit 
Déagol; but Déagol was thereupon to cross the Misty Mountains is foiled by heavy snow, and they are forced to take a path under the mountains, 
the mines of Moria, an ancient dwarf kingdom, now full of Orcs and other evil creatures. During the battle that ensues, Gandalf battles a Balrog 
of Morgoth, and both fall into an abyss. The remaining eight members of the Fellowship escape from Moria and head toward the elf-haven of 
Lothlórien, where they are given gifts from the rulers Celeborn and Galadriel that in many cases prove useful later during the Quest. As 
Frodo tries to decide the future course of the Fellowship, Boromir tries to take the Ring for himself; Frodo ends up putting on the Ring 
to escape from Boromir. While the rest of the Fellowship scatter to hunt for Frodo, Frodo decides that the Fellowship has to be broken,  
and that he must depart secretly for Mordor. Sam insists on coming along, however, and they set off together to Mordor. The Fellowship is 
thus broken.',
			'post_excerpt' => 'Book I: The Ring Sets Out: 
The first chapter in the book begins in a light vein, following the tone of The Hobbit. Bilbo Baggins celebrates his 111th (or eleventy-first, as it 
is called in Hobbiton) birthday on the same day, September 22, that his relative and adopted heir Frodo Baggins celebrates his coming of age 
at 33.',
			'post_status' => 'publish',
			'post_type' => 'mbt_books'
		));
		wp_set_post_terms($post_id, array($fantasy['term_id']), "mbt_genres");
		wp_set_post_terms($post_id, array($journey['term_id']), "mbt_themes");
		wp_set_post_terms($post_id, array($lordoftherings['term_id']), "mbt_series");
		wp_set_post_terms($post_id, array($tolkien['term_id']), "mbt_authors");

		$mbt_main_settings['installed_examples'] = 1;
		update_option("mbt_main_settings", $mbt_main_settings);
	}
}



/*---------------------------------------------------------*/
/* Admin notices                                           */
/*---------------------------------------------------------*/

function mbt_admin_install_notice() {
	?>
	<div class="mbt-install-message">
		<h4><strong>Welcome to MyBookTable</strong> &#8211; You're almost ready to start selling :)</h4>
		<a class="install-button primary" href="<?php echo(admin_url('edit.php?post_type=mbt_books&page=mbt_settings&install_mbt=1')); ?>">Install MyBookTable Pages</a>
		<a class="install-button secondary" href="<?php echo(admin_url('edit.php?post_type=mbt_books&page=mbt_settings&skip_install_mbt=1')); ?>">Skip setup</a>
	</div>
	<?php
}

function mbt_admin_installed_notice() {
	?>
	<div id="message" class="mbt-install-message">
		<h4><strong>MyBookTable has been installed</strong> &#8211; You're ready to start selling :)</h4>
		<a class="install-button primary" href="<?php echo(admin_url('edit.php?post_type=mbt_books&page=mbt_help&finish_install_mbt=1')); ?>">Show Me How</a>
		<a class="install-button secondary" href="<?php echo(admin_url('edit.php?post_type=mbt_books&page=mbt_settings&finish_install_mbt=1')); ?>">Thanks, I Got This</a>
	</div>
	<?php
}

function mbt_admin_notices_styles() {
	global $mbt_main_settings;
	if($mbt_main_settings['installed'] < 2) {
		if($mbt_main_settings['installed'] == 0) {
			if(isset($_GET['install_mbt'])) {
				mbt_install();
				$mbt_main_settings['installed'] = 1;
				update_option("mbt_main_settings", $mbt_main_settings);
			} elseif(isset($_GET['skip_install_mbt']) || $mbt_main_settings['bookstore_page'] != 0) {
				$mbt_main_settings['installed'] = 1;
				update_option("mbt_main_settings", $mbt_main_settings);
			} else {
				add_action('admin_notices', 'mbt_admin_install_notice');
			}
		}
		if($mbt_main_settings['installed'] == 1) {
			if(isset($_GET['finish_install_mbt'])) {
				do_action('mbt_installed');
				$mbt_main_settings['installed'] = 2;
				update_option("mbt_main_settings", $mbt_main_settings);
			} else {
				add_action('admin_notices', 'mbt_admin_installed_notice');
			}
		}
	}
}
add_action('admin_print_styles', 'mbt_admin_notices_styles');



/*---------------------------------------------------------*/
/* Upgrade Database                                        */
/*---------------------------------------------------------*/

function mbt_upgradecheck()
{
	$version = get_option("mbt_version");

	if(empty($version)) {
		$version = mbt_upgrade_initial();
	}

	/*if($version < 1.5) {
		$version = mbt_upgrade_1_5();
	}*/

	update_option("mbt_version", $version);
}

function mbt_upgrade_initial() {
	$defaults = array(
		'installed' => 0,
		'installed_examples' => 0,
		'bookstore_page' => 0,
		'series_in_excerpts' => true,
		'posts_per_page' => false,
		'disable_seo' => false
	);
	update_option("mbt_main_settings", $defaults);

	return '1.0.0';
}

mbt_upgradecheck();
