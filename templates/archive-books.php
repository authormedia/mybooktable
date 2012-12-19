<?php
/*
 * Template Name: Store Archive Page
 */

get_header();
?>

<div id="<?php echo((get_option('template') === 'twentyeleven') ? 'primary' : 'container'); ?>">
	<div id="content" role="main">

			<?php
				if(is_post_type_archive('mbt_books')) {
					echo('<h4>Books</h4>');
				} else if(is_tax('mbt_genres')) {
					echo('<h4>Genre: '.get_queried_object()->name.'</h4>');
				} else if(is_tax('mbt_series')) {
					echo('<h4>Series: '.get_queried_object()->name.'</h4>');
					echo('<div class="series-description">'.mbt_get_series_post(get_queried_object()->slug)->post_content.'</div>');
				} else if(is_tax('mbt_themes')) {
					echo('<h4>Theme: '.get_queried_object()->name.'</h4>');
				}
			?>

			<?php if(have_posts()) { ?>

				<?php while(have_posts()){ the_post(); ?>

					<div <?php post_class(); ?>>

						<?php 
							mbt_show_book_image($post, 175, 250, 'thumbnail alignleft');
							echo('<h3 class="title"><a href="'.get_permalink().'">'.get_the_title().'</a> </h3>');
						?>

						<div class="entry">
							<?php the_excerpt(); ?>
						</div>

					</div><!-- end .post -->

				<?php } ?>

			<?php } else { ?>
				Sorry, nothing here.
			<?php } ?>

	</div><!-- end #content -->
	<?php get_sidebar(); ?>

</div>
<?php get_footer(); ?>