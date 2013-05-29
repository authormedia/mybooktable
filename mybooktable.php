<?php
/*
Plugin Name: MyBookTable
Plugin URI: http://www.authormedia.com/mybooktable/
Description: A WordPress Bookstore Plugin to help authors sell more books.
Author: Author Media
Author URI: http://www.authormedia.com
Version: 0.7.5
*/

define("MBT_VERSION", "0.7.5");

require_once("includes/functions.php");
require_once("includes/setup.php");
require_once("includes/templates.php");
require_once("includes/buybuttons.php");
require_once("includes/admin_pages.php");
require_once("includes/post_types.php");
require_once("includes/taxonomies.php");
require_once("includes/metaboxes.php");
require_once("includes/extras/breadcrumbs.php");
require_once("includes/extras/widgets.php");
require_once("includes/extras/seo.php");



/*---------------------------------------------------------*/
/* Activate Plugin                                         */
/*---------------------------------------------------------*/

function mbt_activate() {
	//flush rewrite rules
	mbt_create_post_types();
	mbt_create_taxonomies();
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}
register_activation_hook(__FILE__, 'mbt_activate');



/*---------------------------------------------------------*/
/* Initialize Plugin                                       */
/*---------------------------------------------------------*/

function mbt_init() {
	do_action('mbt_before_init');

	mbt_load_settings();
	mbt_upgrade_check();

	if(isset($_GET['mbt_uninstall'])) {
		return mbt_uninstall();
	}

	add_filter('pre_set_site_transient_update_plugins', 'mbt_update_check');
	add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'mbt_plugin_action_links');
	add_action('install_plugins_pre_plugin-information', 'mbt_plugin_information');

	if(function_exists('mbtdev_init')) { mbtdev_init(); } else if(function_exists('mbtpro_init')) { mbtpro_init(); }

	do_action('mbt_init');
}
add_action('plugins_loaded', 'mbt_init');

function mbt_plugin_action_links($actions) {
	$actions['settings'] = '<a href="'.admin_url('admin.php?page=mbt_settings').'">Settings</a>';
	return $actions;
}

function mbt_plugin_information() {
	if($_REQUEST['plugin'] == "mybooktable") {
		wp_redirect('http://www.authormedia.com/mybooktable');
		die();
	}
}