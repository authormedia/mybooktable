<div id="<?php echo((get_option('template') === 'twentyeleven') ? 'primary' : 'container'); ?>" <?php if(get_option('template') === 'twentytwelve'){echo('class="site-content"');} ?>>
	<div id="content" role="main">
		<?php if(function_exists('woo_loop_before')) { woo_loop_before(); } ?>