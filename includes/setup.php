<?php

/*---------------------------------------------------------*/
/* Upgrade Database                                        */
/*---------------------------------------------------------*/

function mbt_database_check()
{
	$version = get_option("mbt_version");

	if(empty($version)) {
		mbt_database_initial();
	}

	if($version == "1.0.0") {
		$version == mbt_database_upgrade_0_7_0();
	}

	update_option("mbt_version", MBT_VERSION);
}

function mbt_database_initial() {
	$defaults = array(
		'api_key' => '',
		'installed' => 0,
		'installed_examples' => 0,
		'booktable_page' => 0,
		'style_pack' => 'Default',
		'image_size' => 'medium',
		'enable_socialmedia_badges_single_book' => true,
		'enable_socialmedia_badges_book_excerpt' => true,
		'enable_socialmedia_bar_single_book' => true,
		'enable_seo' => true,
		'series_in_excerpts' => false,
		'posts_per_page' => false
	);
	$defaults = apply_filters("mbt_default_settings", $defaults);
	update_option("mbt_settings", apply_filters("mbt_update_settings", $defaults));
}

function mbt_database_upgrade_0_7_0() {
	global $wpdb;

	mbt_load_settings();
	mbt_verify_api_key();

	$booktable_page = mbt_get_setting('booktable_page');
	if(!empty($booktable_page)) {
		$wpdb->query('UPDATE '.$wpdb->posts.' SET post_content="" WHERE ID = '.$booktable_page.' AND post_content = "[mbt_booktable]"');
	}

	$books = $wpdb->get_col('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = "mbt_book"');
	if(!empty($books)) {
		foreach($books as $book_id) {
			$buybuttons = get_post_meta($book_id, 'mbt_buybuttons', true);
			if(!empty($buybuttons)) {
				foreach($buybuttons as &$button) {
					if(!empty($button['value'])) {
						$button['url'] = $button['value'];
						unset($button['value']);
					}
					$button['display'] = 'featured';
				}
			}
			update_post_meta($book_id, 'mbt_buybuttons', $buybuttons);
		}
	}

	return '0.7.0';
}



/*---------------------------------------------------------*/
/* Installation Functions                                  */
/*---------------------------------------------------------*/

function mbt_install() {
	mbt_install_pages();
	mbt_install_examples();
}

function mbt_install_pages() {
	if(mbt_get_setting('booktable_page') <= 0 or !get_page(mbt_get_setting('booktable_page'))) {
		$post_id = wp_insert_post(array(
			'post_title' => 'Book Table',
			'post_content' => '',
			'post_status' => 'publish',
			'post_type' => 'page'
		));
		mbt_update_setting("booktable_page", $post_id);
	}
}

function mbt_install_examples() {
	if(!mbt_get_setting('installed_examples')) {
		include("examples.php");
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