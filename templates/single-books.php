<?php
/*
 * Template Name: Single Book Page
 */

get_header();
?>

<div id="<?php echo((get_option('template') === 'twentyeleven') ? 'primary' : 'container'); ?>">
	<div id="content" role="main">

		<?php if(function_exists('woo_loop_before')) { woo_loop_before(); } ?>

		<div itemscope="" itemtype="http://schema.org/Product" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="mbt-book-images"><?php echo(mbt_format_image($post->ID)); ?></div>
			<div class="mbt-book-summary">
				<h1 itemprop="name" class="mbt-book-title entry-title">
					<?php echo(get_the_title()); ?>
				</h1>
				<?php echo(mbt_format_price($post->ID)); ?>
				<div class="mbt-book-meta">
					<?php echo(get_the_term_list($post->ID, 'mbt_authors', "Authors: ", ", ")); ?><br>
					<?php echo(get_the_term_list($post->ID, 'mbt_series', "Series: ", ", ")); ?><br>
					<?php echo(get_the_term_list($post->ID, 'mbt_genres', "Genres: ", ", ")); ?><br>
				</div>
				<div itemprop="description" class="mbt-book-blurb"><?php echo($post->post_excerpt); ?></div>
			</div>
			<?php echo(mbt_get_book_buttons($post->ID)); ?>
			<div class="mbt-book-overview">
				<?php echo(apply_filters("the_content", $post->post_content)); ?>
			</div>
			<?php echo(mbt_get_book_series($post->ID)); ?>

			<div style="clear:both;"></div>
		</div>

		<?php if(function_exists('woo_loop_after')) { woo_loop_after(); } ?>
		
	</div>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
