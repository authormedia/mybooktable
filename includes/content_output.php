<?php

/*---------------------------------------------------------*/
/* Content Output Functions                                */
/*---------------------------------------------------------*/

//format price
function mbt_format_price($post_id) {
	$price = get_post_meta($post_id, 'mbt_price', true);
	$oldprice = get_post_meta($post_id, 'mbt_old_price', true);

	if(!empty($price) and !empty($oldprice)) {
		return '<div class="mbt-product-price"><span class="oldprice">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $oldprice), 2).'</span> <span class="newprice">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $price), 2).'</span></div>';
	} elseif(!empty($price)) {
		return '<div class="mbt-product-price">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $price), 2).'</div>';
	}
	return '';
}

//format the buttons for the product
function mbt_get_product_buttons($post_id) {
	global $mbt_main_options;

	$output = '<div class="mbt-product-buttons">';

	$affiliates = mbt_get_affiliates();
	$post_affiliates = get_post_meta($post_id, "mbt_affiliates", true);
	for($i = 0; $i < count($post_affiliates); $i++)
	{
		$output .= $affiliates[$post_affiliates[$i]['type']]['button']($post_affiliates[$i]);
	}

	$output .= '</div>';

	return $output;
}



/*---------------------------------------------------------*/
/* Product Content and Excerpts                            */
/*---------------------------------------------------------*/

add_filter('the_content', 'mbt_product_content', 12);
add_filter('the_excerpt', 'mbt_product_excerpt', 14);

function mbt_product_content($content) {
	global $post, $mbt_main_options;

	//only add for the custom post type
	if(get_post_type() != 'mbt_products'){return $content;}

	$content .= apply_filters('mbt_before_product_content', '<div class="mbt-product mbt-product-content">'); #used to be called buttonstore
	$content .= apply_filters('mbt_product_content_price', mbt_format_price($post->ID));
	$content .= apply_filters('mbt_product_content_author', '<div class="mbt-product-author">by '.get_post_meta($post->ID, 'mbt_product_author', true).'</div>');
	$product_categories = get_the_term_list($post->ID, 'mbt_product_category', '', ', ');
	$content .= apply_filters('mbt_product_content_categories', empty($product_categories) ? '' : '<div class="mbt-product-categories">In Category: '.$product_categories.'</div>');
	$content .= apply_filters('mbt_product_content_buttons', mbt_get_product_buttons($post->ID));
	$content .= apply_filters('mbt_product_content_description', '<div style="clear:both"></div><div class="mbt-product-description">'.wpautop(get_post_meta($post->ID, 'mbt_product_description', true)).'</div>');
	$content .= apply_filters('mbt_after_product_content', '</div>');

	return $content;
}

function mbt_product_excerpt($excerpt) {
	global $post, $mbt_main_options;

	//only add for the custom post type
	if(get_post_type() != 'mbt_products'){return $excerpt;}

	$excerpt .= apply_filters('mbt_before_product_excerpt', '<div class="mbt-product mbt-product-excerpt">');
	$excerpt .= apply_filters('mbt_product_excerpt_price', mbt_format_price($post->ID));
	$excerpt .= apply_filters('mbt_product_excerpt_author', '<div class="mbt-product-author">by '.get_post_meta($post->ID, 'mbt_product_author', true).'</div>');
	$product_categories = get_the_term_list($post->ID, 'product_category', '', ', ');
	$excerpt .= apply_filters('mbt_product_excerpt_categories', empty($product_categories) ? '' : '<div class="mbt-product-categories">In Category: '.$product_categories.'</div>');
	$excerpt .= apply_filters('mbt_product_excerpt_buttons', $mbt_main_options['buttons_in_excerpt'] == "on" ? mbt_get_product_buttons($post->ID) : '');
	$excerpt .= apply_filters('mbt_after_product_excerpt', '</div>');

	return $excerpt;
}

//Add read more links in the appropriate place
add_filter('mbt_product_excerpt_buttons', 'mbt_product_readmore', 20);
function mbt_product_readmore($excerpt) {
	global $mbt_main_options;
	$readmorebutton = empty($mbt_main_options['readmorebutton']) ? 'More Details &raquo;' : $mbt_main_options['readmorebutton'];
	if($mbt_main_options['readmore_buttons_placement'] == 'above'){return '<div class="mbt-read-more"><a class="mbt-read-more-top" href="'.esc_url(get_permalink()).'">'.$readmorebutton.'</a></div>'.$excerpt;}
	if($mbt_main_options['readmore_buttons_placement'] == 'below'){return $excerpt.'<div class="mbt-read-more"><a class="mbt-read-more-bottom" href="'.esc_url(get_permalink()).'">'.$readmorebutton.'</a></div>';}
}

//show related products in the appropriate places
if($mbt_main_options['related_in_content'] == 'on') { add_filter('mbt_product_content_buttons', 'mbt_product_related', 50); }
if($mbt_main_options['related_in_excerpts'] == 'on') { add_filter('mbt_product_excerpt_buttons', 'mbt_product_related', 50); }
function mbt_product_related($content) {
	global $post, $mbt_main_options;

	$output = '';
	$collections = wp_get_post_terms($post->ID, 'product_collection');
	if(!empty($collections)) {
		$output .= '<div class="mbt-related-products">';
		$output .= '<h3>'.apply_filters('mbt_related_products_label', 'Related Products').'</h3>';
		
		foreach ($collections as $collection) {
			if(count($collections) > 1){$output .= '<div class="mbt-collection-name">'.$collection->name.'</div>';}
			$relatedproducts = new WP_Query(array('product_collection' => $collection->slug, 'post__not_in' => array($post->ID)));
			foreach($relatedproducts->posts as $relatedproduct) {
				$output .= '<div class="mbt-product mbt-product-related">';
				if(!is_archive() and $mbt_main_options['related_thumbnail'] == 'on') {
					$output .= '<div class="mbt-product-image"><a href="'.get_permalink($relatedproduct->ID).'">'.mbt_get_product_image($post, 100, 150, 'thumbnail', 'img').'</a></div>';
				}
				$output .= '<div class="mbt-product-link"><a href="'.get_permalink($relatedproduct->ID).'">'.get_the_title().'</a></div>';	
				if(!empty($mbt_main_options['related_descrip_len'])){
					$output .= '<div class="mbt-product-description">'.string_limit_chars($relatedproduct->content, $mbt_main_options['related_descrip_len']).'</div>';	
				}
				$output .= mbt_format_price($relatedproduct->ID);
				$output .= '<div class="mbt-product-buttons">'.mbt_get_product_button($relatedproduct->ID).'</div>';
				$output .= '<div class="clear:both"></div>';
				$output .= '</div>';
			}
		}
		$output .= '</div>';
	}
	return $content.$output;
}

//Insert product ID after category listing. TODO: This should probably be optional
add_filter('mbt_product_content_categories', 'mbt_product_sku', 20);
function mbt_product_sku($content) {
	global $post;
	$sku = get_post_meta($post->ID, 'mbt_product_sku', true);
	if(!empty($sku)) {
		$content .= '<div class="sku">Product ID: '.$sku.'</div>';
	}
	return $content;
}

//If Issuu is turned on, add it to the product content
if($mbt_main_options['use_issuu'] == 'on') { add_filter('mbt_product_content_description', 'mbt_product_issuu', 20); }
function mbt_product_issuu($content) {
	$issuu_id = get_post_meta($post->ID, 'mbt_issuu_id', true);
	if(!empty($issuu_id)) {
		$content .= '<div class="mbt-product-issuu">';
		$content .= '	<h3>Preview</h3>';
		$content .= '	<div>';
		$content .= '		<object style="width:640px;height:472px">';
		$content .= '			<param name="movie" value="http://static.issuu.com/webembed/viewers/style1/v2/IssuuReader.swf?mode=mini&amp;embedBackground=%23000000&amp;backgroundColor=%23222222&amp;documentId='.$issuu_id.'" />';
		$content .= '			<param name="allowfullscreen" value="true"/>';
		$content .= '			<param name="menu" value="false"/>';
		$content .= '			<param name="wmode" value="transparent"/>';
		$content .= '			<embed src="http://static.issuu.com/webembed/viewers/style1/v2/IssuuReader.swf" type="application/x-shockwave-flash" allowfullscreen="true" menu="false" wmode="transparent" style="width:640px;height:472px" flashvars="mode=mini&amp;embedBackground=%23000000&amp;backgroundColor=%23222222&amp;documentId='.$issuu_id.'" />';
		$content .= '		</object>';
		$content .= '	</div>';
		$content .= '</div>';
	}
	return $content;
}