<?php if(mbt_get_setting('socialmedia_in_excerpts')) { ?>
	<div class="mbt-book-upper-socialmedia">
		<iframe src="https://plusone.google.com/_/+1/fastbutton?url=<?php echo(urlencode(get_permalink())); ?>&size=tall&count=true&annotation=bubble" class="gplusone" style="width: 50px; height: 61px; margin: 0px; border: none; overflow: hidden;" frameborder="0" scrolling="no" allowtransparency="true"></iframe>
		<iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo(urlencode(get_permalink())); ?>&layout=box_count" class="fblike" style="width: 50px; height: 61px; margin: 0px; border: none; overflow: hidden;" scrolling="no" frameborder="0" allowtransparency="true"></iframe>
	</div>
<?php } ?>
<h1 itemprop="name" class="mbt-book-title entry-title">
	<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
</h1>