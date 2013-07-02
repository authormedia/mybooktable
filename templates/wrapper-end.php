<?php
if(function_exists('woo_content_before')) {
	echo('</div><!-- /#main -->');
	woo_main_after();
	get_sidebar();
	echo('</div><!-- /#main-sidebar-container -->');
	get_sidebar('alt');
	echo('</div><!-- /#content -->');
	woo_content_after();
} else {
	echo('</div></div>');
}
get_sidebar();
get_footer();