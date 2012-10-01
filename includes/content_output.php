<?php

/*---------------------------------------------------------*/
/* Content Output Functions                                */
/*---------------------------------------------------------*/

//format price
function bt_format_price($post_id) {
	$price = get_post_meta($post_id, 'bt_price', true);
	$oldprice = get_post_meta($post_id, 'bt_old_price', true);

	if(!empty($price) and !empty($oldprice)) {
		return '<div class="bt-product-price"><span class="oldprice">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $oldprice), 2).'</span> <span class="newprice">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $price), 2).'</span></div>';
	} elseif(!empty($price)) {
		return '<div class="bt-product-price">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $price), 2).'</div>';
	}
	return '';
}

//check to see if button code contains image tag
function bt_find_img_tag($buttoncode){ return preg_match("/<img[^<]*>/i", $buttoncode); }

//format to change button code
function bt_change_button_image($buttoncode, $type) {
	$newbutton = '<img src="'.plugins_url('images/'.$type.'_button.png', dirname(__FILE__)).'" border="0" alt="Add to Cart"/>';
	$output = preg_replace("/<img[^<]*>/i", $newbutton, $buttoncode, 1);
	return empty($output) ? $buttoncode : $output;
}

//add button image to the link code
function bt_add_button_image($buttoncode, $type) {
	// check to see that this has a good chance of being a normal url
	if(substr($buttoncode, 0, 4) == 'http') {
		return '<a href="'.$buttoncode.'" target="_blank"><img src="'.plugins_url('images/'.$type.'_button.png', dirname(__FILE__)).'" border="0" alt="Add to Cart"/></a>';
	} else {
		return $buttoncode;
	}
}

//format standard button type
function bt_format_standard_button($buttoncode, $type) {
	return empty($buttoncode) ? '' : '<div class="bt-product-button">'.(bt_find_img_tag($buttoncode) ? bt_change_button_image($buttoncode, $type) : bt_add_button_image($buttoncode, $type)).' </div>'; 
}

//format the buttons for the product
function bt_get_product_buttons($post_id) {
	global $post, $bt_main_options;

	$output = '<div class="bt-product-buttons">'; #prodbutton-row

	foreach(array('amazon', 'cbd', 'bnn', 'ejunkie', 'kickstart', 'nook', 'kindle', 'bim', 'sba') as $service) {
		if($bt_main_options['use_'.$service]=='on') {
			$output .= bt_format_standard_button(get_post_meta($post_id, 'bt_button_code_'.$service, true), $service);
		}
	}

	//if paypal is on, do it's own custom thing
	if($bt_main_options['use_paypal']=='on') {
		$price = get_post_meta($post_id, 'bt_price', true);
		$buttoncode_paypal = get_post_meta($post_id, 'bt_button_code_paypal', true);
		$paypalemail = $bt_main_options['paypal_email'];

		if(!empty($buttoncode_paypal) and !empty($price) and !empty($paypalemail))
		{
			$output .= '<div class="bt-product-button"><form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" class="paypal-form">';

			//if variations are turned on, create and output the options
			if(get_post_meta($post_id, 'bt_paypal_variations_toggle', true) == 'on' and !empty($buttonoptions)) {

				$paypal_options = '';
				$paypal_options_hidden = '';
				for($i = 0; $i < 5; $i++) {
					$paypal_variation_name = get_post_meta($post_id, 'bt_paypal_variation_name_1', true);
					$paypal_variation_price = get_post_meta($post_id, 'bt_paypal_variation_price_1', true);
					$paypal_options .= '<option value="'.$paypal_variation_name.'">'.$paypal_variation_name.' $'.$paypal_variation_price.' USD</option>';
					$paypal_options_hidden .= '<input type="hidden" name="option_select'.$i.'" value="'.$paypal_variation_name.'"><input type="hidden" name="option_amount'.$i.'" value="'.$paypal_variation_price.'">';
				}
				$paypal_variations_label = get_post_meta($post_id, 'bt_paypal_variations_label', true);

				$output .= '<div class="bt-paypal-options">
								<span class="bt-paypal-variations-label">'.$paypal_variations_label.'</span>
								<input type="hidden" name="on0" value="'.$paypal_variations_label.'">
								<select name="os0">'.$paypal_options.'</select>'.$paypal_options_hidden.'
							</div>';
			} else {
				$output .= '<input type="hidden" name="amount" value="'.number_format(preg_replace("/[^0-9,.]/", "", $price), 2).'">';
			}
			
			//output visible buy button
			$output .= '<input type="image" class="bt-paypal-button" title="Add selected item to your shopping basket" value="Add to Cart" src="'.plugins_url('images/paypal_button.png', dirname(__FILE__)).'" border="0">';

			//output hidden paypal vars
			$output .= '<img width="1" height="1" border="0" alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
				<input type="hidden" name="cmd" value="_cart">
				<input type="hidden" name="business" value="'.$paypalemail.'">
				<input type="hidden" name="item_name" value="'.get_the_title($post_id).'">
				<input type="hidden" name="item_number" value="'.get_post_meta($post_id, 'bt_sku', true).'">
				<input type="hidden" name="button_subtype" value="products">
				<input type="hidden" name="weight" value=".01">
				<input type="hidden" name="weight_unit" value="lbs">
				<input type="hidden" name="return" value="'.!empty($bt_main_options['paypal_thankyou_return']) ? get_permalink($bt_main_options['paypal_thankyou_return']) : get_permalink($post_id).'">
				<input type="hidden" name="cancel_return" value="'.!empty($bt_main_options['paypal_cancel_return']) ? get_permalink($bt_main_options['paypal_cancel_return']) : get_permalink($post_id).'">
				<input type="hidden" name="no_shipping" value="2">
				<input type="hidden" name="no_note" value="0">
				<input type="hidden" name="cn" value="Add special instructions to the seller:">
				<input type="hidden" name="lc" value="US">
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="rm" value="1">
				<input type="hidden" name="add" value="1">
				<input type="hidden" name="bn" value="PP-ShopCartBF:btn_cart_LG.gif:NonHosted">
				<input type="hidden" name="option_index" value="0">';

			$output .= '</form></div>';
		}
	}

	$output .= '</div>';

	return $output;
}





/*---------------------------------------------------------*/
/* Product Content and Excerpts                            */
/*---------------------------------------------------------*/

add_filter('the_content', 'bt_product_content', 12);
add_filter('the_excerpt', 'bt_product_excerpt', 14);

function bt_product_content($content) {
	global $post, $bt_main_options;

	//only add for the custom post type
	if(get_post_type() != 'bt_products'){return $content;}

	$content .= apply_filters('bt_before_product_content', '<div class="bt-product bt-product-content">'); #used to be called buttonstore
	$content .= apply_filters('bt_product_content_price', bt_format_price($post->ID));
	$content .= apply_filters('bt_product_content_author', '<div class="bt-product-author">by '.get_post_meta($post->ID, 'bt_product_author', true).'</div>');
	$product_categories = get_the_term_list($post->ID, 'bt_product_category', '', ', ');
	$content .= apply_filters('bt_product_content_categories', empty($product_categories) ? '' : '<div class="bt-product-categories">In Category: '.$product_categories.'</div>');
	$content .= apply_filters('bt_product_content_buttons', bt_get_product_buttons($post->ID));
	$content .= apply_filters('bt_product_content_description', '<div style="clear:both"></div><div class="bt-product-description">'.wpautop(get_post_meta($post->ID, 'bt_product_description', true)).'</div>');
	$content .= apply_filters('bt_after_product_content', '</div>');

	return $content;
}

function bt_product_excerpt($excerpt) {
	global $post, $bt_main_options;

	//only add for the custom post type
	if(get_post_type() != 'bt_products'){return $excerpt;}

	$excerpt .= apply_filters('bt_before_product_excerpt', '<div class="bt-product bt-product-excerpt">');
	$excerpt .= apply_filters('bt_product_excerpt_price', bt_format_price($post->ID));
	$excerpt .= apply_filters('bt_product_excerpt_author', '<div class="bt-product-author">by '.get_post_meta($post->ID, 'bt_product_author', true).'</div>');
	$product_categories = get_the_term_list($post->ID, 'product_category', '', ', ');
	$excerpt .= apply_filters('bt_product_excerpt_categories', empty($product_categories) ? '' : '<div class="bt-product-categories">In Category: '.$product_categories.'</div>');
	$excerpt .= apply_filters('bt_product_excerpt_buttons', $bt_main_options['buttons_in_excerpt'] == "on" ? bt_get_product_buttons($post->ID) : '');
	$excerpt .= apply_filters('bt_after_product_excerpt', '</div>');

	return $excerpt;
}

//Add read more links in the appropriate place
add_filter('bt_product_excerpt_buttons', 'bt_product_readmore', 20);
function bt_product_readmore($excerpt) {
	global $bt_main_options;
	$readmorebutton = empty($bt_main_options['readmorebutton']) ? 'More Details &raquo;' : $bt_main_options['readmorebutton'];
	if($bt_main_options['readmore_buttons_placement'] == 'above'){return '<div class="bt-read-more"><a class="bt-read-more-top" href="'.esc_url(get_permalink()).'">'.$readmorebutton.'</a></div>'.$excerpt;}
	if($bt_main_options['readmore_buttons_placement'] == 'below'){return $excerpt.'<div class="bt-read-more"><a class="bt-read-more-bottom" href="'.esc_url(get_permalink()).'">'.$readmorebutton.'</a></div>';}
}

//show related products in the appropriate places
if($bt_main_options['related_in_content'] == 'on') { add_filter('bt_product_content_buttons', 'bt_product_related', 50); }
if($bt_main_options['related_in_excerpts'] == 'on') { add_filter('bt_product_excerpt_buttons', 'bt_product_related', 50); }
function bt_product_related($content) {
	global $post, $bt_main_options;

	$output = '';
	$collections = wp_get_post_terms($post->ID, 'product_collection');
	if(!empty($collections)) {
		$output .= '<div class="bt-related-products">';
		$output .= '<h3>'.apply_filters('bt_related_products_label', 'Related Products').'</h3>';
		
		foreach ($collections as $collection) {
			if(count($collections) > 1){$output .= '<div class="bt-collection-name">'.$collection->name.'</div>';}
			$relatedproducts = new WP_Query(array('product_collection' => $collection->slug, 'post__not_in' => array($post->ID)));
			foreach($relatedproducts->posts as $relatedproduct) {
				$output .= '<div class="bt-product bt-product-related">';
				if(!is_archive() and $bt_main_options['related_thumbnail'] == 'on') {
					$output .= '<div class="bt-product-image"><a href="'.get_permalink($relatedproduct->ID).'">'.bt_get_product_image($post, 100, 150, 'thumbnail', 'img').'</a></div>';
				}
				$output .= '<div class="bt-product-link"><a href="'.get_permalink($relatedproduct->ID).'">'.get_the_title().'</a></div>';	
				if(!empty($bt_main_options['related_descrip_len'])){
					$output .= '<div class="bt-product-description">'.string_limit_chars($relatedproduct->content, $bt_main_options['related_descrip_len']).'</div>';	
				}
				$output .= bt_format_price($relatedproduct->ID);
				$output .= '<div class="bt-product-buttons">'.bt_get_product_button($relatedproduct->ID).'</div>';
				$output .= '<div class="clear:both"></div>';
				$output .= '</div>';
			}
		}
		$output .= '</div>';
	}
	return $content.$output;
}

//Insert product ID after category listing. TODO: This should probably be optional
add_filter('bt_product_content_categories', 'bt_product_sku', 20);
function bt_product_sku($content) {
	global $post;
	$sku = get_post_meta($post->ID, 'bt_product_sku', true);
	if(!empty($sku)) {
		$content .= '<div class="sku">Product ID: '.$sku.'</div>';
	}
	return $content;
}

//If Issuu is turned on, add it to the product content
if($bt_main_options['use_issuu'] == 'on') { add_filter('bt_product_content_description', 'bt_product_issuu', 20); }
function bt_product_issuu($content) {
	$issuu_id = get_post_meta($post->ID, 'bt_issuu_id', true);
	if(!empty($issuu_id)) {
		$content .= '<div class="bt-product-issuu">';
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