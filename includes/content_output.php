<?php

/*---------------------------------------------------------*/
/* Content Output Functions                                */
/*---------------------------------------------------------*/

//format price
function mbt_format_price($post_id) {
	$price = get_post_meta($post_id, 'mbt_price', true);
	$oldprice = get_post_meta($post_id, 'mbt_old_price', true);

	if(!empty($price) and !empty($oldprice)) {
		return '<div class="mbt-book-price"><span class="oldprice">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $oldprice), 2).'</span> <span class="newprice">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $price), 2).'</span></div>';
	} elseif(!empty($price)) {
		return '<div class="mbt-book-price">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $price), 2).'</div>';
	}
	return '';
}

//format the buttons for the book
function mbt_get_book_buttons($post_id) {
	global $mbt_main_settings;

	$output = '<div class="mbt-book-buttons">';

	$affiliates = mbt_get_affiliates();
	$post_affiliates = get_post_meta($post_id, "mbt_affiliates", true);
	if(!empty($post_affiliates)) {
		for($i = 0; $i < count($post_affiliates); $i++)
		{
			$output .= $affiliates[$post_affiliates[$i]['type']]['button']($post_affiliates[$i]);
		}
	}

	$output .= '</div>';

	return $output;
}



/*---------------------------------------------------------*/
/* Book Content and Excerpts                            */
/*---------------------------------------------------------*/

add_filter('the_content', 'mbt_book_content', 20);
add_filter('the_excerpt', 'mbt_book_excerpt', 20);

function mbt_book_content($content) {
	global $post, $mbt_main_settings;

	//only add for the custom post type
	if(get_post_type() != 'mbt_books'){return $content;}

	$content .= apply_filters('mbt_before_book_content', '<div class="mbt-book mbt-book-content">');
	#$content .= apply_filters('mbt_book_content_image', '<div class="mbt-book-image">'.mbt_get_book_image($post, 200, 300, 'thumbnail', 'img').'</div>');
	$content .= apply_filters('mbt_book_content_price', mbt_format_price($post->ID));
	$content .= apply_filters('mbt_book_content_author', '<div class="mbt-book-author">by '.get_the_term_list($post->ID, 'mbt_authors', '', ', ').'</div>');
	$book_categories = get_the_term_list($post->ID, 'mbt_genres', '', ', ');
	$content .= apply_filters('mbt_book_content_categories', empty($book_categories) ? '' : '<div class="mbt-book-categories">Genres: '.$book_categories.'</div>');
	$content .= apply_filters('mbt_book_content_buttons', mbt_get_book_buttons($post->ID));
	$content .= apply_filters('mbt_book_content_description', '<div style="clear:both"></div><div class="mbt-book-description">'.wpautop($post->post_excerpt).'</div>');
	$content .= apply_filters('mbt_after_book_content', '</div>');

	return $content;
}

function mbt_book_excerpt($excerpt) {
	global $post, $mbt_main_settings;

	//only add for the custom post type
	if(get_post_type() != 'mbt_books'){return $excerpt;}

	$excerpt .= apply_filters('mbt_before_book_excerpt', '<div class="mbt-book mbt-book-excerpt">');
	$excerpt .= apply_filters('mbt_book_excerpt_price', mbt_format_price($post->ID));
	$excerpt .= apply_filters('mbt_book_excerpt_author', '<div class="mbt-book-author">by '.get_the_term_list($post->ID, 'mbt_authors', '', ', ').'</div>');
	$book_categories = get_the_term_list($post->ID, 'mbt_genres', '', ', ');
	$excerpt .= apply_filters('mbt_book_excerpt_categories', empty($book_categories) ? '' : '<div class="mbt-book-categories">Genres: '.$book_categories.'</div>');
	//$excerpt .= apply_filters('mbt_book_excerpt_buttons', $mbt_main_settings['buttons_in_excerpt'] == "on" ? mbt_get_book_buttons($post->ID) : '');
	$excerpt .= apply_filters('mbt_after_book_excerpt', '</div>');

	return $excerpt;
}

//Add read more links in the appropriate place
add_filter('mbt_book_excerpt_buttons', 'mbt_book_readmore', 20);
function mbt_book_readmore($excerpt) {
	global $mbt_main_settings;
	$readmorebutton = do_filters('mbt_read_more_text', 'More Info &raquo;');
	//if($mbt_main_settings['readmore_buttons_placement'] == 'above'){return '<div class="mbt-read-more"><a class="mbt-read-more-top" href="'.esc_url(get_permalink()).'">'.$readmorebutton.'</a></div>'.$excerpt;}
	//if($mbt_main_settings['readmore_buttons_placement'] == 'below'){return $excerpt.'<div class="mbt-read-more"><a class="mbt-read-more-bottom" href="'.esc_url(get_permalink()).'">'.$readmorebutton.'</a></div>';}
	return $excerpt.'<div class="mbt-read-more"><a class="mbt-read-more-bottom" href="'.esc_url(get_permalink()).'">'.$readmorebutton.'</a></div>';
}

//Todo: add hooks and filters to related books (series) for stuff like image
//show related books in the appropriate places
//add_filter('mbt_book_content_buttons', 'mbt_book_related', 50);
//if($mbt_main_settings['series_in_excerpts']){add_filter('mbt_book_excerpt_buttons', 'mbt_book_related', 50);}
function mbt_book_related($content) {
	global $post, $mbt_main_settings;

	$output = '';
	$collections = wp_get_post_terms($post->ID, 'book_collection');
	if(!empty($collections)) {
		$output .= '<div class="mbt-related-books">';
		$output .= '<h3>'.apply_filters('mbt_related_books_label', 'Related Books').'</h3>';
		
		foreach ($collections as $collection) {
			if(count($collections) > 1){$output .= '<div class="mbt-collection-name">'.$collection->name.'</div>';} //change styling
			$relatedbooks = new WP_Query(array('book_collection' => $collection->slug, 'post__not_in' => array($post->ID)));
			foreach($relatedbooks->posts as $relatedbook) {
				$output .= '<div class="mbt-book mbt-book-related">';
				$output .= '<div class="mbt-book-image"><a href="'.get_permalink($relatedbook->ID).'">'.mbt_get_book_image($post, 100, 150, 'thumbnail', 'img').'</a></div>';
				$output .= '<div class="mbt-book-link"><a href="'.get_permalink($relatedbook->ID).'">'.get_the_title().'</a></div>';	
				if(!empty($mbt_main_settings['related_descrip_len'])){
					$output .= '<div class="mbt-book-description">'.string_limit_chars($relatedbook->content, $mbt_main_settings['related_descrip_len']).'</div>';	
				}
				$output .= mbt_format_price($relatedbook->ID);
				$output .= '<div class="mbt-book-buttons">'.mbt_get_book_button($relatedbook->ID).'</div>';
				$output .= '<div class="clear:both"></div>';
				$output .= '</div>';
			}
		}
		$output .= '</div>';
	}
	return $content.$output;
}

//Insert book ID
add_filter('mbt_book_content_categories', 'mbt_book_sku', 20);
function mbt_book_sku($content) {
	global $post;
	$sku = get_post_meta($post->ID, 'mbt_book_sku', true);
	if(!empty($sku)) {
		$content .= '<div class="sku">Book ID: '.$sku.'</div>';
	}
	return $content;
}