<?php

/*---------------------------------------------------------*/
/* Check for Upgrades                                      */
/*---------------------------------------------------------*/

function mbt_upgrade_check()
{
	$version = mbt_get_setting("version");

	if($version < "0.7.4") { $version = mbt_database_upgrade_0_7_4(); }

	if($version != MBT_VERSION) { mbt_update_setting("version", MBT_VERSION); }
}

function mbt_database_upgrade_0_7_4() {
	global $mbt_settings;
	$mbt_settings['version'] = MBT_VERSION;
	$mbt_settings['installed'] = 'done';
	update_option("mbt_settings", $mbt_settings);
	mbt_verify_api_key();
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

function mbt_admin_notices_init() {
	add_action('admin_init', 'mbt_add_admin_notices', 20);
}
add_action('mbt_init', 'mbt_admin_notices_init');

function mbt_add_admin_notices() {
	if(!mbt_get_setting('installed')) {
		if(isset($_GET['install_mbt'])) {
			mbt_install();
			mbt_update_setting('installed', 'check_api_key');
		} elseif(isset($_GET['skip_install_mbt']) || mbt_get_setting('booktable_page') != 0) {
			mbt_update_setting('installed', 'check_api_key');
		} else {
			add_action('admin_notices', 'mbt_admin_install_notice');
		}
	}
	if(mbt_get_setting('installed') == 'check_api_key') {
		if(!mbt_get_setting('api_key') and (defined('MBTPRO_VERSION') or defined('MBTDEV_VERSION'))) {
			add_action('admin_notices', 'mbt_admin_setup_api_key_notice');
		} else {
			mbt_update_setting('installed', 'post_install');
		}
	}
	if(mbt_get_setting('installed') == 'post_install') {
		if(isset($_GET['finish_install_mbt'])) {
			do_action('mbt_installed');
			mbt_update_setting('installed', 'done');
		} else {
			add_action('admin_notices', 'mbt_admin_installed_notice');
		}
	}
	if(mbt_get_setting('installed') == 'done') {
		if(!mbt_get_setting('api_key') and (defined('MBTPRO_VERSION') or defined('MBTDEV_VERSION'))) {
			add_action('admin_notices', 'mbt_admin_setup_api_key_notice');
		}
	}

	if(isset($_GET['mbt_install_examples'])) {
		mbt_install_examples();
	}

	if(isset($_GET['mbt_install_pages'])) {
		mbt_install_pages();
	}
}

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

function mbt_admin_setup_api_key_notice() {
	?>
	<div id="message" class="mbt-install-message">
		<h4><strong>Setup your API Key</strong> &#8211; MyBookTable needs your API key to enable enhanced features</h4>
		<a class="install-button primary" href="<?php echo(admin_url('admin.php?page=mbt_settings&setup_api_key=1')); ?>">Go To Settings</a>
	</div>
	<?php
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