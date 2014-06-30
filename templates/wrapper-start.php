<?php
get_header();

$template = get_option('template');

if($template == 'twentyeleven') {
	echo('<div id="primary"><div id="content" role="main">');
} else if($template == 'twentytwelve') {
	echo('<div id="primary" class="site-content"><div id="content" role="main">');
} else if($template == 'twentythirteen') {
	echo('<div id="primary" class="site-content"><div id="content" role="main" class="entry-content twentythirteen">');
} else if($template == 'twentyfourteen') {
	echo('<div id="primary" class="content-area"><div id="content" role="main" class="entry-content site-content"><div class="entry-content">');
}  else if(function_exists('woo_content_before')) {
	woo_content_before();
	echo('<div id="content" class="col-full">');
	echo('<div id="main-sidebar-container">');
	woo_main_before();
	echo('<div id="main" class="col-left">');
	woo_loop_before();
} else  if(function_exists('genesis')) {
	get_header();
	do_action('genesis_before_content_sidebar_wrap');
	echo('<div id="content-sidebar-wrap">');
	do_action('genesis_before_content');
	echo('<div id="content" class="hfeed content">');
	do_action('genesis_before_loop');
} else {
	echo '<div id="container"><div id="content" role="main">';
}
