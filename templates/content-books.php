<div itemscope="" itemtype="http://schema.org/Product" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="mbt-book-images">
		<?php echo('<a href="'.get_permalink().'">'.mbt_format_image($post->ID).'</a>');?>
	</div>
	<div class="mbt-book-summary">
		<h1 itemprop="name" class="mbt-book-title entry-title">
			<?php echo('<a href="'.get_permalink().'">'.get_the_title().'</a>'); ?>
		</h1>
		<?php echo(mbt_format_price($post->ID)); ?>
		<div class="mbt-book-meta">
			<?php echo(get_the_term_list($post->ID, 'mbt_authors', "Authors: ", ", ", "<br>")); ?>
			<?php echo(get_the_term_list($post->ID, 'mbt_series', "Series: ", ", ", "<br>")); ?>
			<?php echo(get_the_term_list($post->ID, 'mbt_genres', "Genres: ", ", ", "<br>")); ?>
		</div>
		<div itemprop="description" class="mbt-book-blurb">
			<?php
				echo($post->post_excerpt);
				echo(apply_filters('mbt_excerpt_more', ' <a href="'.get_permalink().'">More info →</a>'));
			?>
		</div>
	</div>
	<?php echo(mbt_format_book_buttons($post->ID, true)); ?>
	<?php if(mbt_get_setting('socialmedia_in_excerpts') and mbt_is_socialmedia_active()) { echo(mbt_format_socialmedia($post->ID)); } ?>
	<?php if(mbt_get_setting('series_in_excerpts')) { echo(mbt_format_book_series($post->ID)); } ?>
	<div style="clear:both;"></div>
</div>