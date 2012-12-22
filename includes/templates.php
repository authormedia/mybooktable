<?php

/*---------------------------------------------------------*/
/* Template Overload Functions                             */
/*---------------------------------------------------------*/

//enqueue frontend plugin styles
add_action('wp_enqueue_scripts', 'mbt_enqueue_styles');
function mbt_enqueue_styles() {
	wp_enqueue_style('book-table-style', plugins_url('css/frontend-style.css', dirname(__FILE__)));
}

function mbt_load_book_templates($template) {
	if(is_post_type_archive('mbt_books') or is_tax('mbt_genres') or is_tax('mbt_series')) {
		$locatedtemplate = locate_template('archive-books.php');
		$template = empty($locatedtemplate) ? plugin_dir_path(dirname(__FILE__)).'templates/archive-books.php' : $locatedtemplate;
	}

	if(is_singular('mbt_books')) {
		$locatedtemplate = locate_template('single-books.php');
		$template = empty($locatedtemplate) ? plugin_dir_path(dirname(__FILE__)).'templates/single-books.php' : $locatedtemplate;
	}

	return $template;
}
add_filter('archive_template', 'mbt_load_book_templates');
add_filter('single_template', 'mbt_load_book_templates');



// change the number of posts per page for the book archives
function mbt_set_books_posts_per_page($query) {
	if(get_post_type() != 'mbt_books' and $query->is_main_query()) { // is only for min_book archives and only on main query
		$query->query_vars['posts_per_page'] = !empty($mbt_get_settings['posts_per_page']) ? mbt_get_setting('posts_per_page') : get_option('posts_per_page');
	}
}
add_action('pre_get_posts', 'mbt_set_books_posts_per_page');




function mbt_booktable_shortcode( $atts ) {
?>
	<?php
		global $wp_query, $post;
		$old_wp_query = $wp_query;
		$wp_query = new WP_Query(array('post_type' => 'mbt_books'));
	?>
	
	<?php if(have_posts()) { ?>

		<?php while(have_posts()){ the_post(); ?>

				<?php include(dirname(dirname(__FILE__))."/templates/excerpt-books.php"); ?>

		<?php } ?>

	<?php } else { ?>

		<article id="post-0" class="post no-results not-found">
			<header class="entry-header">
				<h1 class="entry-title"><?php _e('Nothing Found', 'twentyeleven'); ?></h1>
			</header><!-- .entry-header -->

			<div class="entry-content">
				<p><?php _e('Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'twentyeleven'); ?></p>
				<?php get_search_form(); ?>
			</div><!-- .entry-content -->
		</article><!-- #post-0 -->

	<?php } ?>

<?php
	$wp_query = $old_wp_query;
}
add_shortcode('mbt_booktable', 'mbt_booktable_shortcode');






/*---------------------------------------------------------*/
/* Content Output Functions                                */
/*---------------------------------------------------------*/

function mbt_format_image($post_id) {
	$url = '';
	$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'post-thumbnail');
	if(!empty($thumb)) {
		$url = $thumb['0'];
	}
	if(empty($url)) {
		$url = plugins_url('images/book-placeholder.png', dirname(__FILE__));
	}
	return '<a itemprop="image" href="'.$url.'" class="zoom" rel="thumbnails">
				<img width="225" height="300" src="'.$url.'" class="wp-post-image">
			</a>';
}

//format price
function mbt_format_price($post_id) {
	$price = get_post_meta($post_id, 'mbt_price', true);
	$sale_price = get_post_meta($post_id, 'mbt_sale_price', true);

	if(!empty($price) and !empty($sale_price)) {
		return '<div itemprop="offers" itemscope="" itemtype="http://schema.org/Offer" class="mbt-book-price">
					<span itemprop="price" class="price">
						<span class="oldprice">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $price), 2).'</span>
						<span class="newprice">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $sale_price), 2).'</span>
					</span>
				</div>';
	} elseif(!empty($price)) {
		return '<div itemprop="offers" itemscope="" itemtype="http://schema.org/Offer" class="mbt-book-price">
					<span itemprop="price" class="price">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $price), 2).'</span>
				</div>';
	}
	return '';
}

//format the buttons for the book
function mbt_get_book_buttons($post_id) {
	$output = '<div class="mbt-book-buttons">';

	$buybuttons = mbt_get_buybuttons();
	$post_buybuttons = get_post_meta($post_id, "mbt_buybuttons", true);
	if(!empty($post_buybuttons)) {
		for($i = 0; $i < count($post_buybuttons); $i++)
		{
			$output .= $buybuttons[$post_buybuttons[$i]['type']]['button']($post_buybuttons[$i]);
		}
	}

	$output .= '</div>';

	return $output;
}














// /*---------------------------------------------------------*/
// /* Book Content and Excerpts                            */
// /*---------------------------------------------------------*/

// add_filter('the_content', 'mbt_book_content', 20);
// add_filter('the_excerpt', 'mbt_book_excerpt', 20);

// function mbt_book_content($content) {
// 	global $post;

// 	//only add for the custom post type
// 	if(get_post_type() != 'mbt_books'){return $content;}

// 	$content .= apply_filters('mbt_before_book_content', '<div class="mbt-book mbt-book-content">');
// 	#$content .= apply_filters('mbt_book_content_image', '<div class="mbt-book-image">'.mbt_get_book_image($post, 200, 300, 'thumbnail', 'img').'</div>');
// 	$content .= apply_filters('mbt_book_content_price', mbt_format_price($post->ID));
// 	$content .= apply_filters('mbt_book_content_author', '<div class="mbt-book-author">by '.get_the_term_list($post->ID, 'mbt_authors', '', ', ').'</div>');
// 	$book_categories = get_the_term_list($post->ID, 'mbt_genres', '', ', ');
// 	$content .= apply_filters('mbt_book_content_categories', empty($book_categories) ? '' : '<div class="mbt-book-categories">Genres: '.$book_categories.'</div>');
// 	$content .= apply_filters('mbt_book_content_buttons', mbt_get_book_buttons($post->ID));
// 	$content .= apply_filters('mbt_book_content_description', '<div style="clear:both"></div><div class="mbt-book-description">'.wpautop($post->post_excerpt).'</div>');
// 	$content .= apply_filters('mbt_after_book_content', '</div>');

// 	return $content;
// }

// function mbt_book_excerpt($excerpt) {
// 	global $post;

// 	//only add for the custom post type
// 	if(get_post_type() != 'mbt_books'){return $excerpt;}

// 	$excerpt .= apply_filters('mbt_before_book_excerpt', '<div class="mbt-book mbt-book-excerpt">');
// 	$excerpt .= apply_filters('mbt_book_excerpt_price', mbt_format_price($post->ID));
// 	$excerpt .= apply_filters('mbt_book_excerpt_author', '<div class="mbt-book-author">by '.get_the_term_list($post->ID, 'mbt_authors', '', ', ').'</div>');
// 	$book_categories = get_the_term_list($post->ID, 'mbt_genres', '', ', ');
// 	$excerpt .= apply_filters('mbt_book_excerpt_categories', empty($book_categories) ? '' : '<div class="mbt-book-categories">Genres: '.$book_categories.'</div>');
// 	//$excerpt .= apply_filters('mbt_book_excerpt_buttons', mbt_get_setting('buttons_in_excerpt') == "on" ? mbt_get_book_buttons($post->ID) : '');
// 	$excerpt .= apply_filters('mbt_after_book_excerpt', '</div>');

// 	return $excerpt;
// }

// //Add read more links in the appropriate place
// add_filter('mbt_book_excerpt_buttons', 'mbt_book_readmore', 20);
// function mbt_book_readmore($excerpt) {
// 	$readmorebutton = do_filters('mbt_read_more_text', 'More Info &raquo;');
// 	//if(mbt_get_setting('readmore_buttons_placement') == 'above'){return '<div class="mbt-read-more"><a class="mbt-read-more-top" href="'.esc_url(get_permalink()).'">'.$readmorebutton.'</a></div>'.$excerpt;}
// 	//if(mbt_get_setting('readmore_buttons_placement') == 'below'){return $excerpt.'<div class="mbt-read-more"><a class="mbt-read-more-bottom" href="'.esc_url(get_permalink()).'">'.$readmorebutton.'</a></div>';}
// 	return $excerpt.'<div class="mbt-read-more"><a class="mbt-read-more-bottom" href="'.esc_url(get_permalink()).'">'.$readmorebutton.'</a></div>';
// }

// //Todo: add hooks and filters to related books (series) for stuff like image
// //show related books in the appropriate places
// //add_filter('mbt_book_content_buttons', 'mbt_book_related', 50);
// //if(mbt_get_setting('series_in_excerpts')){add_filter('mbt_book_excerpt_buttons', 'mbt_book_related', 50);}
// /*function mbt_book_related($content) {
// 	global $post;

// 	$output = '';
// 	$collections = wp_get_post_terms($post->ID, 'book_collection');
// 	if(!empty($collections)) {
// 		$output .= '<div class="mbt-related-books">';
// 		$output .= '<h3>'.apply_filters('mbt_related_books_label', 'Related Books').'</h3>';
		
// 		foreach ($collections as $collection) {
// 			if(count($collections) > 1){$output .= '<div class="mbt-collection-name">'.$collection->name.'</div>';} //change styling
// 			$relatedbooks = new WP_Query(array('book_collection' => $collection->slug, 'post__not_in' => array($post->ID)));
// 			foreach($relatedbooks->posts as $relatedbook) {
// 				$output .= '<div class="mbt-book mbt-book-related">';
// 				$output .= '<div class="mbt-book-image"><a href="'.get_permalink($relatedbook->ID).'">'.mbt_get_book_image($post, 100, 150, 'thumbnail', 'img').'</a></div>';
// 				$output .= '<div class="mbt-book-link"><a href="'.get_permalink($relatedbook->ID).'">'.get_the_title().'</a></div>';	
// 				if(!empty(mbt_get_setting('related_descrip_len'))){
// 					$output .= '<div class="mbt-book-description">'.string_limit_chars($relatedbook->content, mbt_get_setting('related_descrip_len')).'</div>';	
// 				}
// 				$output .= mbt_format_price($relatedbook->ID);
// 				$output .= '<div class="mbt-book-buttons">'.mbt_get_book_button($relatedbook->ID).'</div>';
// 				$output .= '<div class="clear:both"></div>';
// 				$output .= '</div>';
// 			}
// 		}
// 		$output .= '</div>';
// 	}
// 	return $content.$output;
// }*/

// //Insert book ID
// add_filter('mbt_book_content_categories', 'mbt_book_sku', 20);
// function mbt_book_sku($content) {
// 	global $post;
// 	$sku = get_post_meta($post->ID, 'mbt_book_sku', true);
// 	if(!empty($sku)) {
// 		$content .= '<div class="sku">Book ID: '.$sku.'</div>';
// 	}
// 	return $content;
// }