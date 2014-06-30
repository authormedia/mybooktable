<?php

$template = get_option('template');

if($template == 'twentyfourteen') {
	echo('</div></div></div>');
	get_sidebar();
	get_footer();
} else if(function_exists('woo_content_before')) {
	woo_loop_after();
	echo('</div><!-- /#main -->');
	woo_main_after();
	get_sidebar();
	echo('</div><!-- /#main-sidebar-container -->');
	get_sidebar('alt');
	echo('</div><!-- /#content -->');
	woo_content_after();
	get_footer();
} else if(function_exists('genesis')) {
	do_action( 'genesis_after_loop' );
	echo('</div><!-- end #content -->');
	do_action('genesis_after_content');
	echo('</div><!-- end #content-sidebar-wrap -->');
	do_action( 'genesis_after_content_sidebar_wrap' );
	get_footer();
} else {
	echo('</div></div>');
	get_sidebar();
	get_footer();
}