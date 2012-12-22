<div itemscope="" itemtype="http://schema.org/Product" id="product-64" class="post-64 product type-product status-publish hentry">
	<div class="images">
		<?php echo(mbt_format_image($post->ID)); ?>
	</div>
	<div class="summary">
		<h1 itemprop="name" class="product_title entry-title"><?php the_title(); ?></h1>
		<?php echo(mbt_format_price($post->ID)); ?>
		<div itemprop="description">
			<?php the_excerpt(); ?>
		</div>
	</div>
</div>