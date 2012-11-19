<?php
/*
 * Template Name: Store Archive/Category Page
 */

global $mbt_main_options, $woo_options, $wp_query;
$is_woo = $mbt_main_options['is_woo'] == 'on';
get_header();
?>

<?php if($is_woo=='on'){woo_content_before();} ?>

<div id="content" class="col-full">
	<div id="main-sidebar-container">

        <?php if($is_woo){woo_main_before();} ?>
        <div id="main" class="col-left">
 
			<?php if($is_woo){woo_loop_before();} ?>

			<?php
				$thetitle = '<h1>'.get_post_type_object('min_products')->labels->name.'</h1>';
				//if true then this is the store homepage and not just a category page
				if(get_query_var('taxonomy') == 'product_category') {
					$taxonomy_obj = $wp_query->get_queried_object();
					$thetitle .= '<h4>Category: '.$taxonomy_obj->name.'</h4>';
				}
				echo($thetitle); 
			?>

			<?php if(have_posts()){ ?>

				<?php
					//if it's an archive, show the category description
					if(is_archive()) {
						$term = get_term_by('slug', get_query_var('product_category'), 'product_category');

						if(empty($term->description)){
							echo('<div class="no-category-description"></div>');
						} else {
							echo('<div class="category-description">'.$term->description.'</div>');
						}
					}
				?>
				<div class="fix"></div>

				<?php while(have_posts()){ the_post(); ?>

					<?php if($is_woo){woo_post_before();} ?>

					<div <?php post_class(); ?>>

						<?php if($is_woo){woo_post_inside_before();} ?>

						<?php
							$title_before = '<h1 class="title">';
							$title_after = '</h1>';

							if(!is_single()){
								$title_before .= '<h3 class="title"> <a href="'.get_permalink(get_the_ID()).'" rel="bookmark" title="'.the_title_attribute(array('echo'=>0)).'">';
								$title_after = '</a> </h3>';
							}

							the_title($title_before, $title_after);
						?>

						<?php
							if($is_woo){
								if($woo_options['woo_post_content'] != 'content') {
									mbt_show_product_image($post, 175, 250, 'thumbnail alignleft');
								}
							}
						?>
						<div class="entry">
						    <?php
								if($is_woo) {
							    	if(!is_404() || is_page_template('template-blog.php') || is_page_template('template-magazine.php')) {
										remove_action('woo_post_inside_after', 'woo_post_more');
									}

							    	if($woo_options['woo_post_content'] == 'content') {
							    		the_content('Read Full Post &rarr;');
							    	} else {
							    		the_excerpt();
							    	}

							    	if($woo_options['woo_post_content'] == 'content') {
							    		wp_link_pages(apply_filters('woothemes_pagelinks_args', array('before' => '<div class="page-link">'.__('Pages:', 'woothemes'), 'after' => '</div>')));
							    	}
								} else { // if not woo we simply show the excerpt
									the_excerpt();  
								}
						    ?>
						</div><!-- end .entry -->
						<div class="fix"></div>

						<?php if($is_woo){woo_post_inside_after();} ?>

					</div><!-- end .post -->
				<?php } // end while have_posts() ?>
			<?php } else { get_template_part('content', 'noposts');} ?>

	 		<?php if($is_woo){ woo_loop_after(); woo_pagenav(); } ?>

    	</div><!-- end #main -->

        <?php if($is_woo){woo_main_after();} ?>

        <?php get_sidebar(); ?>

	</div><!-- end #main-sidebar-container -->

	<?php get_sidebar('alt'); ?>

</div><!-- end #content -->

<?php if($is_woo){woo_content_after();} ?>

<?php get_footer(); ?>