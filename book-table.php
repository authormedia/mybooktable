<?php
/*
Plugin Name: MyBookTable
Plugin URI: http://www.mybooktable.com
Description: A simple store plugin for books, allowing you to integrate with external shopping carts and booktable websites.
Author: Castle Media Group
Version: 0.3.0
*/

require_once("includes/functions.php");
require_once("includes/setup.php");
require_once("includes/templates.php");
require_once("includes/buybuttons.php");
require_once("includes/admin_pages.php");
require_once("includes/post_types.php");
require_once("includes/metaboxes.php");
require_once("includes/breadcrumbs.php");
require_once("includes/seo.php");

register_activation_hook(__FILE__, 'mbt_activate');