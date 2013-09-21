<div class="mbt-book-overview">
	<h3 class="mbt-book-overview-title"><?php _e('Book Overview', 'mybooktable'); ?></h3>
	<?php global $post; echo(apply_filters("the_content", $post->post_content)); ?>
</div>
