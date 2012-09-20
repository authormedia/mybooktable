<?php
/**
 * Store Category Template
 *
 */
 $main_options = get_option("button-store-options_options"); 
 if ($main_options['is_woo']=='on') {
 global $woo_options;
 }
 get_header();
?>      
    <!-- #content Starts -->
	<?php if ($main_options['is_woo']=='on') {woo_content_before();} ?>

    <div id="content" class="col-full">
    	<div id="main-sidebar-container">    
            <!-- #main Starts -->
            <?php woo_main_before(); ?>
            <div id="main" class="col-left">
		
<?php 

	//	echo store_breadcrumbs();
	
	// discover if this is the home page or a category page
	$tax = get_query_var('taxonomy');    
	if ( $tax == 'product_category') {
		$is_prodcat == true; // if true then this is the bookstore homepage and not just a category page.
		}
			
		$taxonomy_obj = $wp_query->get_queried_object();
		$prodlabel = show_product_label();
		$thetitle .= '<h1>'.$prodlabel.'</h1>';
		
		if($is_prodcat){ // show only if this is an archive, not the home page
		$thetitle .= '<h4>Category: '.$taxonomy_obj->name.'</h4>';
		}
		
 global $more; $more = 0;
 
 if ($main_options['is_woo']=='on') {woo_loop_before();}
if (have_posts()) { $count = 0;

echo $thetitle;

echo cat_description();
?>

<div class="fix"></div>

<?php
	while (have_posts()) { the_post(); $count++;

		

 $title_before = '<h1 class="title">';
 $title_after = '</h1>';
 
 if ( ! is_single() ) {
 
 	$title_before = '<h3 class="title">';
 	$title_after = '</h3>';
 
	$title_before = $title_before . '<a href="' . get_permalink( get_the_ID() ) . '" rel="bookmark" title="' . the_title_attribute( array( 'echo' => 0 ) ) . '">';
	$title_after = '</a>' . $title_after;
 
 }
 
if ($main_options['is_woo']=='on') {
	 $page_link_args = apply_filters( 'woothemes_pagelinks_args', array( 'before' => '<div class="page-link">' . __( 'Pages:', 'woothemes' ), 'after' => '</div>' ) );
 
	 woo_post_before();
}
?>
<div <?php post_class(); ?>>
<?php
if ($main_options['is_woo']=='on') {
	woo_post_inside_before();
}	
	the_title( $title_before, $title_after );
 if ( $woo_options['woo_post_content'] != 'content' AND !is_singular() ){
 		 if (function_exists('woo_image')) {
		woo_image( 'width='.$woo_options['woo_thumb_w'].'&height='.$woo_options['woo_thumb_h'].'&class=thumbnail '.$woo_options['woo_thumb_align'] );
		} else {
		castleimage(175,250,'thumbnail alignleft');
		}
	}

?>
	<div class="entry">
	    <?php
		if ($main_options['is_woo']=='on') {
	    		if ( ! is_singular() && ! is_404() || is_page_template( 'template-blog.php' ) || is_page_template( 'template-magazine.php' ) ) {
			remove_action( 'woo_post_inside_after', 'woo_post_more' );
		}
	    	if ( $woo_options['woo_post_content'] == 'content' || is_single() ) { the_content(__('Read Full Post &rarr;', 'woothemes') ); } else { the_excerpt(); }
	    	if ( $woo_options['woo_post_content'] == 'content' || is_singular() ) wp_link_pages( $page_link_args );
		} else { // if not woo we simply show the excerpt
			the_excerpt();  
		}
	    ?>
	</div><!-- /.entry -->
	<div class="fix"></div>
<?php
if ($main_options['is_woo']=='on') {
	woo_post_inside_after();
}
?>
</div><!-- /.post -->
<?php
	/*woo_post_after();*/
	
	} // End WHILE Loop
} else {
	get_template_part( 'content', 'noposts' );
} // End IF Statement

 if ($main_options['is_woo']=='on') { 
 woo_loop_after(); 
 woo_pagenav();
 }
?>

            </div><!-- /#main -->

            <?php if ($main_options['is_woo']=='on') { woo_main_after();} ?>

            <?php get_sidebar(); ?>
    
		</div><!-- /#main-sidebar-container -->         

		<?php get_sidebar( 'alt' ); ?>       

    </div><!-- /#content -->

	<?php if ($main_options['is_woo']=='on') { woo_content_after();} ?>

<?php get_footer(); ?>