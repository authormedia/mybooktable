<?php

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
		'booktable_page' => 0,
		'book_image_size' => 'large',
		'series_in_excerpts' => false,
		'socialmedia_in_excerpts' => false,
		'posts_per_page' => false,
		'featured_buybuttons' => array('amazon' => 'on'),
		'disable_socialmedia' => false,
		'disable_seo' => false
	);
	$defaults = apply_filters("mbt_default_settings", $defaults);
	update_option("mbt_settings", apply_filters("mbt_update_settings", $defaults));

	return '1.0.0';
}



/*---------------------------------------------------------*/
/* Installation Functions                                  */
/*---------------------------------------------------------*/

function mbt_install() {
	mbt_install_pages();
	mbt_install_examples();
}

function mbt_install_pages() {
	if(mbt_get_setting('booktable_page') <= 0) {
		$post_id = wp_insert_post(array(
			'post_title' => 'Booktable',
			'post_content' => '[mbt_booktable]',
			'post_status' => 'publish',
			'post_type' => 'page'
		));
		mbt_update_setting("booktable_page", $post_id);
	}
}

function mbt_install_examples() {
	if(!mbt_get_setting('installed_examples')) {

		//Create Genre
		$fantasy = wp_insert_term('Fantasy', 'mbt_genre', array('slug' => 'fantasy'));

		//Create Series
		$lordoftherings = wp_insert_term('The Lord of the Rings', 'mbt_series', array('slug' => 'lordoftherings'));

		//Create Author
		$tolkien = wp_insert_term('J. R. R. Tolkien', 'mbt_author', array('slug' => 'tolkien'));

		//Create Books
		$post_id = wp_insert_post(array(
			'post_title' => 'The Fellowship of the Ring',
			'post_content' => 'Book I: The Ring Sets Out: The first chapter in the book begins in a light vein, following the tone of The Hobbit. Bilbo Baggins celebrates his 111th (or eleventy-first, as it is called in Hobbiton) birthday on the same day, September 22, that his relative and adopted heir Frodo Baggins celebrates his coming of age at 33. At the birthday party, Bilbo departs from the Shire, the land of the Hobbits, for what he calls a permanent holiday. He leaves Frodo his remaining belongings, including his home, Bag End, and (after some persuasion by the wizard Gandalf) the Ring he had found on his adventures (which he used to make himself invisible). Gandalf leaves on his own business, warning Frodo to keep the Ring secret. Over the next 17 years Gandalf periodically pays short visits to Bag End. One spring night, he arrives to warn Frodo about the truth of Bilbo\'s ring; it is the One Ring of Sauron the Dark Lord. Sauron forged it to subdue and rule Middle-earth, but in the War of the Last Alliance, he was defeated by Gil-galad the Elven King and Elendil, High King of Arnor and Gondor, though they themselves perished in the deed. Isildur, Elendil\'s son, cut the Ring from Sauron\'s finger. Sauron was thus overthrown, but the Ring itself was not destroyed as Isildur kept it for himself. Isildur was slain soon afterwards in the Battle of the Gladden Fields, and the Ring was lost in Great River Anduin. Thousands of years later, it was found by the hobbit Déagol; but Déagol was thereupon to cross the Misty Mountains is foiled by heavy snow, and they are forced to take a path under the mountains, the mines of Moria, an ancient dwarf kingdom, now full of Orcs and other evil creatures. During the battle that ensues, Gandalf battles a Balrog of Morgoth, and both fall into an abyss. The remaining eight members of the Fellowship escape from Moria and head toward the elf-haven of Lothlórien, where they are given gifts from the rulers Celeborn and Galadriel that in many cases prove useful later during the Quest. As Frodo tries to decide the future course of the Fellowship, Boromir tries to take the Ring for himself; Frodo ends up putting on the Ring to escape from Boromir. While the rest of the Fellowship scatter to hunt for Frodo, Frodo decides that the Fellowship has to be broken,  and that he must depart secretly for Mordor. Sam insists on coming along, however, and they set off together to Mordor. The Fellowship is thus broken.',
			'post_excerpt' => 'Book I: The Ring Sets Out: The first chapter in the book begins in a light vein, following the tone of The Hobbit. Bilbo Baggins celebrates his 111th (or eleventy-first, as it is called in Hobbiton) birthday on the same day, September 22, that his relative and adopted heir Frodo Baggins celebrates his coming of age at 33.',
			'post_status' => 'publish',
			'post_type' => 'mbt_book'
		));
		wp_set_post_terms($post_id, array($fantasy['term_id']), "mbt_genre");
		wp_set_post_terms($post_id, array($lordoftherings['term_id']), "mbt_series");
		wp_set_post_terms($post_id, array($tolkien['term_id']), "mbt_author");

		update_post_meta($post_id, "mbt_buybuttons", unserialize('a:1:{i:0;a:2:{s:4:"type";s:6:"amazon";s:5:"value";s:35:"http://www.amazon.com/dp/B007978NPG";}}'));

		mbt_update_setting('installed_examples', true);
	}
}



/*---------------------------------------------------------*/
/* Admin notices                                           */
/*---------------------------------------------------------*/

function mbt_admin_install_notice() {
	?>
	<div class="mbt-install-message">
		<h4><strong>Welcome to MyBookTable</strong> &#8211; You're almost ready to start promoting your books :)</h4>
		<a class="install-button primary" href="<?php echo(admin_url('admin.php?page=mbt_settings&install_mbt=1')); ?>">Install MyBookTable Pages</a>
		<a class="install-button secondary" href="<?php echo(admin_url('admin.php?page=mbt_settings&skip_install_mbt=1')); ?>">Skip setup</a>
	</div>
	<?php
}

function mbt_admin_installed_notice() {
	?>
	<div id="message" class="mbt-install-message">
		<h4><strong>MyBookTable has been installed</strong> &#8211; You're ready to start promoting your books :)</h4>
		<a class="install-button primary" href="<?php echo(admin_url('admin.php?page=mbt_help&finish_install_mbt=1')); ?>">Show Me How</a>
		<a class="install-button secondary" href="<?php echo(admin_url('admin.php?page=mbt_settings&finish_install_mbt=1')); ?>">Thanks, I Got This</a>
	</div>
	<?php
}

function mbt_admin_init() {
	if(mbt_get_setting('installed') < 2) {
		if(mbt_get_setting('installed') == 0) {
			if(isset($_GET['install_mbt'])) {
				mbt_install();
				mbt_update_setting('installed', 1);
			} elseif(isset($_GET['skip_install_mbt']) || mbt_get_setting('booktable_page') != 0) {
				mbt_update_setting('installed', 1);
			} else {
				add_action('admin_notices', 'mbt_admin_install_notice');
			}
		}
		if(mbt_get_setting('installed') == 1) {
			if(isset($_GET['finish_install_mbt'])) {
				do_action('mbt_installed');
				mbt_update_setting('installed', 2);
			} else {
				add_action('admin_notices', 'mbt_admin_installed_notice');
			}
		}
	}

	if(isset($_GET['mbt_install_examples'])) {
		mbt_install_examples();
	}

	if(isset($_GET['mbt_install_pages'])) {
		mbt_install_pages();
	}
}



/*---------------------------------------------------------*/
/* Uninstallation Functions                                */
/*---------------------------------------------------------*/

function mbt_uninstall() {
	//erase options
	delete_option('mbt_settings');

	//erase taxonomies
	mbt_erase_taxonomy('mbt_author');
	mbt_erase_taxonomy('mbt_series');
	mbt_erase_taxonomy('mbt_genre');

	//erase books
	global $wpdb;
	$wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'mbt_book'");

	//erase plugin
	$active_plugins = get_option('active_plugins');
	$plugin = plugin_basename(dirname(dirname(__FILE__))."/mybooktable.php");
	unset($active_plugins[array_search($plugin, $active_plugins)]);
	update_option('active_plugins', $active_plugins);
}

function mbt_erase_taxonomy($name) {
	global $wpdb;
	$wpdb->query("DELETE term_rel.* FROM $wpdb->term_relationships AS term_rel INNER JOIN $wpdb->term_taxonomy AS term_tax WHERE term_rel.term_taxonomy_id = term_tax.term_taxonomy_id AND term_tax.taxonomy = '".$name."'");
	$wpdb->query("DELETE FROM $wpdb->term_taxonomy WHERE taxonomy = '".$name."'");
}