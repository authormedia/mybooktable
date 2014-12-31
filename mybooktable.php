<?php
/*
Plugin Name: MyBookTable - WordPress Affiliate Bookstore
Plugin URI: http://www.authormedia.com/mybooktable/
Description: A WordPress Bookstore Plugin to help authors sell more books.
Author: Author Media
Author URI: http://www.authormedia.com
Text Domain: mybooktable
Version: 1.3.7
*/

define("MBT_VERSION", "1.3.7");

require_once("includes/functions.php");
require_once("includes/setup.php");
require_once("includes/templates.php");
require_once("includes/buybuttons.php");
require_once("includes/admin_pages.php");
require_once("includes/post_types.php");
require_once("includes/taxonomies.php");
require_once("includes/metaboxes.php");
require_once("includes/extras/seo.php");
require_once("includes/extras/widgets.php");
require_once("includes/extras/shortcodes.php");
require_once("includes/extras/compatibility.php");
require_once("includes/extras/googleanalytics.php");
require_once("includes/extras/breadcrumbs.php");
require_once("includes/extras/goodreads.php");
require_once("includes/extras/booksorting.php");
require_once("includes/extras/getnoticed.php");



/*---------------------------------------------------------*/
/* Activate Plugin                                         */
/*---------------------------------------------------------*/

function mbt_activate() {
	mbt_register_post_types();
	mbt_register_taxonomies();
	flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'mbt_activate');

function mbt_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'mbt_deactivate');



/*---------------------------------------------------------*/
/* Initialize Plugin                                       */
/*---------------------------------------------------------*/

function mbt_init() {
	if($GLOBALS['pagenow'] == "plugins.php" and current_user_can('install_plugins') and isset($_GET['action']) and $_GET['action'] == 'deactivate' and isset($_GET['plugin']) and $_GET['plugin'] == plugin_basename(dirname(__FILE__)).'/mybooktable.php') { return; }
	if($GLOBALS['pagenow'] == "plugins.php" and current_user_can('install_plugins') and isset($_GET['mbt_uninstall'])) { return mbt_uninstall(); }

	if(function_exists('mbtdev_init') and (!defined('MBTPRO_VERSION') or (defined('MBTPRO_VERSION') and version_compare(MBTDEV_VERSION, MBTPRO_VERSION) >= 0))) { add_action('mbt_init', 'mbtdev_init'); } else if(function_exists('mbtpro_init')) { add_action('mbt_init', 'mbtpro_init'); }

	do_action('mbt_before_init');

	load_plugin_textdomain('mybooktable', false, plugin_basename(dirname(__FILE__))."/i18n");
	mbt_load_settings();
	mbt_upgrade_check();
	mbt_customize_plugins_page();
	add_filter('pre_set_site_transient_update_plugins', 'mbt_update_check');
	add_action('init', 'mbt_rewrites_check', 999);

	do_action('mbt_init');
}
add_action('plugins_loaded', 'mbt_init');

function mbt_rewrites_check() {
	global $wp_rewrite;
	$rules = $wp_rewrite->wp_rewrite_rules();
	if(!isset($rules[mbt_get_product_slug()."/?$"]) or $rules[mbt_get_product_slug()."/?$"] !== "index.php?post_type=mbt_book") { flush_rewrite_rules(); }
}

function mbt_customize_plugins_page() {
	add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'mbt_plugin_action_links');
	add_filter('plugin_row_meta', 'mbt_plugin_row_meta', 10, 2);
}

function mbt_plugin_action_links($actions) {
	$actions['settings'] = '<a href="'.admin_url('admin.php?page=mbt_settings').'">'.__('Settings', 'mybooktable').'</a>';
	return $actions;
}

function mbt_plugin_row_meta($links, $file) {
	if ($file == plugin_basename(__FILE__)) {
		$links[] = '<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/mybooktable?filter=5#postform">'.__('Review this plugin', 'mybooktable').'</a>';
	}
	return $links;
}
