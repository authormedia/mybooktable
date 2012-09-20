<?php
/*-----------------------------------------------------------------------------------*/
/* Content Output functions                                                       */
/*-----------------------------------------------------------------------------------*/

// todo - make add_product_content function and the one for excerpts work from the same code base. So much is common between the two functions, they should be one function.
function add_product_content($content) {
global $post, $options;
	if ( 'min_products' != get_post_type() ) {
		return $content; //exit;
	}
	// Get the options
	$main_options = get_option("button-store-options_options");  
	$paypalemail = $main_options['paypal_email'];

	// get the products homepage - added by Jim Camomile
	$ptobj = get_post_type_object('min_products');
	$productshome = '/'.$ptobj->rewrite['slug'].'/';

	$original = $content;
	$thetitle = get_the_title($post->ID);
	$prodtags = get_the_term_list($post->ID, 'product_tag','',', ');
	$prodcats = get_the_term_list($post->ID, 'product_category','',', ');
	$rawprice = get_post_meta($post->ID, '_cmb_price', true);
	// strip out non-numerics and add decimals to price
	$price = preg_replace("/[^0-9,.]/", "", $rawprice);
	$price = number_format($price,2);
	$price = '$'.$price;
	// salesprice - used for kickstart only
	$rawsalesprice = get_post_meta($post->ID, '_cmb_salesprice', true);
	// strip out non-numerics and add decimals to price
	$salesprice = preg_replace("/[^0-9,.]/", "", $rawsalesprice);
	$salesprice = number_format($salesprice,2);
	$salesprice = '$'.$salesprice;
	

	$sku = get_post_meta($post->ID, '_cmb_sku', true);
	$author = get_post_meta($post->ID, '_cmb_author', true);
	$descrip = get_post_meta($post->ID, '_cmb_additional_description', true);
	//$prodcats
	$proddetails = '<div id="buttonstore">';

// if this is kickstart cart, we need to add the price and the sale price
	if( $main_options['use_kickstart']=='on' && ( !empty($rawprice) || !empty($rawsalesprice))) {
		if ( !empty($rawprice) && !empty($rawsalesprice) ){
			$proddetails .= '<div class="price"><span class="oldprice">'.$price.'</span> <span class="newprice">'.$salesprice.'</span></div>';
		} elseif (!empty($rawprice)) {
			$proddetails .= '<div class="price">'.$price.'</div>';
		} else {
			$proddetails .= '<div class="price">'.$salesprice.'</div>';
		}
	}

	if (!empty($author)){
		$proddetails .= '<div class="author">by '.$author.'</div>';
	}
	
	if (!empty($prodcats)){
		$proddetails .= '<div class="prodcats">In Category: '.$prodcats.'</div>';
	}


	if (!empty($buttoncode_paypal) && !empty($price)  && !empty($paypalemail) && $paypal_variations_toggle != 'on'){
	//if ((!empty($price) || $price > 0) && $main_options['use_paypal']=='on' && $paypal_variations_toggle != 'on'){
		$proddetails .= '<div class="price"><h3>Paypal Price: '.$price.'</h3></div>';
	}	

// Show for single pages only. Tried to apply conditional to only load this when hook is the_content only... detect if this is firing on the_content using has_filter. (if (has_filter('the_content','add_product_content')==12) { )  ..reveal the following if it is loading
// as priortiy 12.. just to make it specific. This hides stuff when firing in the excerpt.
if (!is_archive()) {	
	if (!empty($sku)){
		$proddetails .= '<div class="sku">Product ID: '.$sku.'</div>';
	}
} // end show for content only	

	// change the buttons for each button type and display it. 
	// Todo, roll this long sprawl of conditionals into a more elegant loop calling each button type from an array.

//if (has_filter('the_content','add_product_content')==12) {	
if (!is_archive()) {	
if (!empty($main_options['above_button_bar'])){
	$proddetails .= '<div class="abovebar">'.$main_options['above_button_bar'].'</div>';
}
} // end show for content only	

// Show the buttons
$proddetails .=  buttoncode($post->ID);
//echo $post->ID;
//if (has_filter('the_content','add_product_content')==12) {	
if (!is_archive()) {	
if (!empty($main_options['below_button_bar'])){ 
	$proddetails .= '<div class="belowbar">'.$main_options['below_button_bar'].'</div>';	
}	
} // end show for content only	


// Related Products go here
// conditional for showing on excerpt or content
$proddetails .= '<div style="clear: both;"></div>';
$proddetails .= '<div id="relatedbooks"><h3>Related Books</h3>'.relatedproducts($post->ID).'</div>';
// End Related Products


// Show for single pages only. Tried to apply conditional to only load this when hook is the_content only... detect if this is firing on the_content using has_filter. (if (has_filter('the_content','add_product_content')==12) { )  ..reveal the following if it is loading
// as priortiy 12.. just to make it specific. This hides stuff when firing in the excerpt.
if (!is_archive()) {	
	if (!empty($descrip)){
		$proddetails .= '<div class="descrip">'.$descrip.'</div>';
	}
} // end show for content only	



	$proddetails .= '<div style="clear: both;"></div>';
	
	$proddetails .= '</div><div id="buttonstorebot"></div>';
	
	$thecontent = $original.$proddetails;
	return $thecontent;
}
add_filter('the_content', 'add_product_content', 12);
add_filter('the_excerpt', 'add_product_content', 14);



function buttoncode($postID) {
	// get the options from custom-admin-pages options
	$main_options = get_option("button-store-options_options");  
	$paypalemail = $main_options['paypal_email'];
	
	$paypal_variations_toggle = get_post_meta($postID, '_cmb_paypal_variations_toggle', true);
	$paypal_variations_label = get_post_meta($postID, '_cmb_paypal_variations_label', true);
	
	$paypal_variation_name_1 = get_post_meta($postID, '_cmb_paypal_variation_name_1', true);
	$paypal_variation_price_1 = get_post_meta($postID, '_cmb_paypal_variation_price_1', true);

	$paypal_variation_name_2 = get_post_meta($postID, '_cmb_paypal_variation_name_2', true);
	$paypal_variation_price_2 = get_post_meta($postID, '_cmb_paypal_variation_price_2', true);

	$paypal_variation_name_3 = get_post_meta($postID, '_cmb_paypal_variation_name_3', true);
	$paypal_variation_price_3 = get_post_meta($postID, '_cmb_paypal_variation_price_3', true);

	$paypal_variation_name_4 = get_post_meta($postID, '_cmb_paypal_variation_name_4', true);
	$paypal_variation_price_4 = get_post_meta($postID, '_cmb_paypal_variation_price_4', true);

	$paypal_variation_name_5 = get_post_meta($postID, '_cmb_paypal_variation_name_5', true);
	$paypal_variation_price_5 = get_post_meta($postID, '_cmb_paypal_variation_price_5', true);
	// End Paypal Vars
		
	if($main_options['use_amazon']=='on'){
		$buttoncode_ama = get_post_meta($postID, '_cmb_button_code_amazon', true);
	}
	if($main_options['use_cbd']=='on'){
		$buttoncode_cbd = get_post_meta($postID, '_cmb_button_code_cbd', true);
	}
	if($main_options['use_bnn']=='on'){
		$buttoncode_bnn = get_post_meta($postID, '_cmb_button_code_bnn', true);
	}
	if($main_options['use_ejunkie']=='on'){
		$buttoncode_ejunk = get_post_meta($postID, '_cmb_button_code_ejunkie', true);
	}
	if($main_options['use_kickstart']=='on'){
		$buttoncode_kick = get_post_meta($postID, '_cmb_button_code_kickstart', true);
	}
	if($main_options['use_bnn_nook']=='on'){	
		$buttoncode_nook = get_post_meta($postID, '_cmb_button_code_nook', true);
	}
	
	if($main_options['use_kindle']=='on'){	
		$buttoncode_kindle = get_post_meta($postID, '_cmb_button_code_kindle', true);
	}
	if($main_options['use_bim']=='on'){	
		$buttoncode_bim = get_post_meta($postID, '_cmb_button_code_bim', true);
	}
	if($main_options['use_sba']=='on'){	
		$buttoncode_sba = get_post_meta($postID, '_cmb_button_code_sba', true);
	}
	
	if($main_options['use_paypal']=='on'){	
		$buttoncode_paypal = get_post_meta($postID, '_cmb_button_code_paypal', true);
	}	
	
	// start output string
	$buttonoutput = '';

	if (!empty($buttoncode_ama)){
		// detect if an image tag is in the code
		$is_button = find_img_tag($buttoncode_ama); 
			if ($is_button){
				// if so then swap the image
				$ama_button = change_button_image($buttoncode_ama,'amazon');
				$buttonoutput .= '<div class="prodbutton">'.$ama_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$ama_button = add_button_image($buttoncode_ama,'amazon'); 
				$buttonoutput .= '<div class="prodbutton">'.$ama_button .'</div>';
		}
	}
	if (!empty($buttoncode_cbd)){
		// detect if an image tag is in the code
		$is_button = find_img_tag($buttoncode_cbd); 
			if ($is_button){
				// if so then swap the image
				$cbd_button = change_button_image($buttoncode_cbd,'cbd');
				$buttonoutput .= '<div class="prodbutton">'.$cbd_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$cbd_button = add_button_image($buttoncode_cbd,'cbd'); 
				$buttonoutput .= '<div class="prodbutton">'.$cbd_button .'</div>';
		}
	}
	if (!empty($buttoncode_bnn)){
		// detect if an image tag is in the code
		$is_button = find_img_tag($buttoncode_bnn); 
			if ($is_button){
				// if so then swap the image
				$bnn_button = change_button_image($buttoncode_bnn,'bnn');
				$buttonoutput .= '<div class="prodbutton">'.$bnn_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$bnn_button = add_button_image($buttoncode_bnn,'bnn'); 
				$buttonoutput .= '<div class="prodbutton">'.$bnn_button .'</div>';
		}
	}
	if (!empty($buttoncode_ejunk)){
		// detect if an image tag is in the code
		$is_button = find_img_tag($buttoncode_ejunk); 
			if ($is_button){
				// if so then swap the image
				$ejunk_button = change_button_image($buttoncode_ejunk,'ejunkie');
				$buttonoutput .= '<div class="prodbutton">'.$ejunk_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$ejunk_button = add_button_image($buttoncode_ejunk,'ejunkie'); 
				$buttonoutput .= '<div class="prodbutton">'.$ejunk_button .'</div>';
		}
	}
	if (!empty($buttoncode_kick)){
		// detect if an image tag is in the code
		$is_button = find_img_tag($buttoncode_kick); 
			if ($is_button){
				// if so then swap the image
				$kick_button = change_button_image($buttoncode_kick,'kickstart');
				$buttonoutput .= '<div class="prodbutton">'.$kick_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$kick_button = add_button_image($buttoncode_kick,'kickstart'); 
				$buttonoutput .= '<div class="prodbutton">'.$bnn_button .'</div>';
		}
	}
	if (!empty($buttoncode_nook)){
		// detect if an image tag is in the code
		$is_button = find_img_tag($buttoncode_nook); 
			if ($is_button){
				// if so then swap the image
				$nook_button = change_button_image($buttoncode_nook,'nook');
				$buttonoutput .= '<div class="prodbutton">'.$nook_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$nook_button = add_button_image($buttoncode_nook,'nook'); 
				$buttonoutput .= '<div class="prodbutton">'.$nook_button .'</div>';
		}
	}
	
	if (!empty($buttoncode_kindle)){
		// detect if an image tag is in the code
		$is_button = find_img_tag($buttoncode_kindle); 
			if ($is_button){
				// if so then swap the image
				$kindle_button = change_button_image($buttoncode_kindle,'kindle');
				$buttonoutput .= '<div class="prodbutton">'.$kindle_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$kindle_button = add_button_image($buttoncode_kindle,'kindle'); 
				$buttonoutput .= '<div class="prodbutton">'.$kindle_button .'</div>';
		}
	}
	if (!empty($buttoncode_bim)){
		// detect if an image tag is in the code
		$is_button = find_img_tag($buttoncode_bim); 
			if ($is_button){
				// if so then swap the image
				$bim_button = change_button_image($buttoncode_bim,'bim');
				$buttonoutput .= '<div class="prodbutton">'.$bim_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$bim_button = add_button_image($buttoncode_bim,'bim'); 
				$buttonoutput .= '<div class="prodbutton">'.$bim_button .'</div>';
		}
	}
	if (!empty($buttoncode_sba)){
		// detect if an image tag is in the code
		$is_button = find_img_tag($buttoncode_sba); 
			if ($is_button){
				// if so then swap the image
				$sba_button = change_button_image($buttoncode_sba,'sba');
				$buttonoutput .= '<div class="prodbutton">'.$sba_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$sba_button = add_button_image($buttoncode_sba,'sba'); 
				$buttonoutput .= '<div class="prodbutton">'.$sba_button .'</div>';
		}
	}
	
	// START PAYPAL	
	
	if (!empty($buttoncode_paypal) && !empty($price)  && !empty($paypalemail)){
	
	// set the return and cancel pages
	if (!empty($main_options['paypal_thankyou_return'])){
			$paypal_thanks = $main_options['paypal_thankyou_return'];
		}else{
			$paypal_thanks = $productshome;
		}
	if (!empty($main_options['paypal_cancel_return'])){
			$paypal_cancel = $main_options['paypal_cancel_return'];
		}else{
			$paypal_cancel = $productshome;
		}
	
	// set the button
	$ppbutton = plugins_url('images/paypal_button.png',__FILE__);
	
	$buttonoutput .= '<div class="prodbutton">';
	
	// create button options by forming 5 options statically.<br />
	// todo - find way to form these more dynamically
	
	$buttonoptions = '';
	$optionrefs = '';
	if (!empty($paypal_variation_price_1)){
		$buttonoptions .= '<option value="'.$paypal_variation_name_1.'">'.$paypal_variation_name_1.' $'.$paypal_variation_price_1.' USD</option>';
		$optionrefs .= '<input type="hidden" name="option_select0" value="'.$paypal_variation_name_1.'"><input type="hidden" name="option_amount0" value="'.$paypal_variation_price_1.'">';
	}
	if (!empty($paypal_variation_price_2)){
		$buttonoptions .= '<option value="'.$paypal_variation_name_2.'">'.$paypal_variation_name_2.' $'.$paypal_variation_price_2.' USD</option>';
		$optionrefs .= '<input type="hidden" name="option_select1" value="'.$paypal_variation_name_2.'"><input type="hidden" name="option_amount1" value="'.$paypal_variation_price_2.'">';
	
	}
	if (!empty($paypal_variation_price_3)){
		$buttonoptions .= '<option value="'.$paypal_variation_name_3.'">'.$paypal_variation_name_3.' $'.$paypal_variation_price_3.' USD</option>';
		$optionrefs .= '<input type="hidden" name="option_select2" value="'.$paypal_variation_name_3.'"><input type="hidden" name="option_amount2" value="'.$paypal_variation_price_3.'">';
	
	}
	if (!empty($paypal_variation_price_4)){
		$buttonoptions .= '<option value="'.$paypal_variation_name_4.'">'.$paypal_variation_name_4.' $'.$paypal_variation_price_4.' USD</option>';
		$optionrefs .= '<input type="hidden" name="option_select3" value="'.$paypal_variation_name_4.'"><input type="hidden" name="option_amount3" value="'.$paypal_variation_price_4.'">';
	
	}
	if (!empty($paypal_variation_price_5)){
		$buttonoptions .= '<option value="'.$paypal_variation_name_5.'">'.$paypal_variation_name_5.' $'.$paypal_variation_price_5.' USD</option>';
		$optionrefs .= '<input type="hidden" name="option_select4" value="'.$paypal_variation_name_5.'"><input type="hidden" name="option_amount4" value="'.$paypal_variation_price_5.'">';
	}
	
	$showprice = '';
	if (empty($buttonoptions) || $paypal_variations_toggle != 'on'){
		$theoptions = '<input type="hidden" name="amount" value="'.$price.'">';
		} else {
		$theoptions ='<table cellpadding="6" cellspacing="6" class="paypal-options">
						<tr><td><input type="hidden" name="on0" value="'.$paypal_variations_label.'">'.$paypal_variations_label.'</td></tr><tr><td><select name="os0">'.$buttonoptions.'</select> </td></tr></table>';
		$formclass = 'paypal-form';
		}
	
	// not sure if the return and cancel return controls will work. These were not in the original form but copied from an older one.	
	$buttonoutput .=  '	
	<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" class="'.$formclass.'">
	'.$theoptions.' 
	<input type="image" class="paypal-button" title="Add selected item to your shopping basket" value="Add to Cart" src="'.$ppbutton.'" border="0">
	<img width="1" height="1" border="0" alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif">
	<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
	<input type="hidden" name="cmd" value="_cart"> 
	
	<input type="hidden" name="business" value="'.$paypalemail.'">
	<input type="hidden" name="item_name" value="'.$thetitle.'">
	<input type="hidden" name="item_number" value="'.$sku.'">
	<input type="hidden" name="button_subtype" value="products">
	
	<input type="hidden" name="weight" value=".01">
	<input type="hidden" name="weight_unit" value="lbs">
	<input type="hidden" name="return" value="'.$paypal_thanks.'">
	<input type="hidden" name="cancel_return" value="'.$paypal_cancel.'">
	<input type="hidden" name="no_shipping" value="2">
	<input type="hidden" name="no_note" value="0">
	<input type="hidden" name="cn" value="Add special instructions to the seller:">
	<input type="hidden" name="lc" value="US">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="rm" value="1">
	<input type="hidden" name="add" value="1">
	<input type="hidden" name="bn" value="PP-ShopCartBF:btn_cart_LG.gif:NonHosted">
	'.$optionrefs.'
	<input type="hidden" name="option_index" value="0">
	</form>
	';
	// <input type="hidden" name="page_style" value="Primary">
	// <input type="hidden" name="quantity" value="1">
	// 
	$buttonoutput .= '</div>';
				
		}// if not empty paypal
	// END PAYPAL
	return $buttonoutput;
}

// Related Products Function
function relatedproducts($postID) {
	//return 'the related books for ID-'.$postID;
	$collections = wp_get_post_terms($postID,'product_collection',$args);
	$output = '';
	//var_dump($collections);
	$numcollections = count($collections);
//	print_r($numcollections);
	foreach ($collections as $collection) {
		$name = $collection->name;
		$slug = $collection->slug;
		if ($numcollections > 1){
		$output .= '<div class="name">'.$name.'</div>';
		}
			$relatedbooks = new WP_Query( array( 'product_collection' => $slug, 'post__not_in' => array($postID) ) );
				while ($relatedbooks->have_posts()) : $relatedbooks->the_post();
					//print_r( $relatedbooks);
					$postyID = get_the_id();
					$buttoncode = buttoncode($postyID);
				//	print_r($buttoncode);
				//	$proddetails .=  buttoncode($post->ID);
					$output .= '<div class="book"><a href="'.get_permalink().'">'.get_the_title().'</a></div>';
					$output .= '<div class="book">11'.$buttoncode.'22</a></div>';
				endwhile;			
	} // end foreach 
	return $output;
}

?>