<?php

/*---------------------------------------------------------*/
/* Content Output Functions                                */
/*---------------------------------------------------------*/

//check to see if button code contains image tag
function bt_find_img_tag($buttoncode){ return preg_match("/<img[^<]*>/i", $buttoncode); }

//format to change button code
function bt_change_button_image($buttoncode, $type) {
	$pattern = "/<img[^<]*>/i"; // detect the img tag to replace it with regex preg_replace
	$thebutton = plugins_url('images/'.$type.'_button.png', __FILE__); // use the second arg to load the correct button
	$newbutton = '<img src="'.$thebutton.'" border="0" class="buttonstore-btn" alt="Add to Cart"/>';
	
	if (preg_replace($pattern,$newbutton,$buttoncode)){
		return preg_replace($pattern,$newbutton,$buttoncode,1);
	} else {
		return $buttoncode;
	}
}

//add button image to the link code
function bt_add_button_image($buttoncode, $type) {
	// use the second arg to load the correct button
	$thebutton = plugins_url('images/'.$type.'_button.png',__FILE__);
	$newbutton = '<img src="'.$thebutton.'" border="0" class="buttonstore-btn" alt="Add to Cart"/>';
	// check to see that this has a good chance of being a normal url
	if (substr($buttoncode,0,4)=='http'){ 
		// if good url, form it into a link tag and return it
		$linkbutton = '<a href="'.$buttoncode.'" target="_blank">'.$newbutton.'</a>';
		return $linkbutton;
	} else { 
		// just leave it as it is
		return $buttoncode;
	}
}

//finds related products
function bt_get_related_products($postID) {
	global $bt_main_options; 
	$related_label = (!empty($bt_main_options['related_label']) ? $bt_main_options['related_label'] : 'Related Products') ;

	$has_thumbnail = $bt_main_options['related_thumbnail']; // on or blank
	$has_descrip = $bt_main_options['related_descrip'];
	$related_descrip_len = (!empty($bt_main_options['related_descrip_len']) ? $bt_main_options['related_descrip_len'] : 120) ; // set to selected length or 200 as default
	  
	//return 'the related books for ID-'.$postID;
	$collections = wp_get_post_terms($postID,'product_collection',$args);
	if ($collections){
		$output = '';
		$output .= '<div class="bt_get_related_products">';
		$output .= '<h3>'.$related_label.'</h3>';
		$numcollections = count($collections);
		
		foreach ($collections as $collection) {
			$name = $collection->name;
			$slug = $collection->slug;
			if ($numcollections > 1){
			$output .= '<div class="name">'.$name.'</div>';
			}
				$relatedbooks = new WP_Query( array( 'product_collection' => $slug, 'post__not_in' => array($postID) ) );
					while ($relatedbooks->have_posts()) : $relatedbooks->the_post();
						$output .= '<div class="product">';
						if( !is_archive() && $has_thumbnail=='on'){ 
							$image = getcastleimage(100,150,'thumbnail','img');
							$output .= '<div class="image"><a href="'.get_permalink().'">'.$image.'</a></div>';
						}
						$output .= '<div class="link"><a href="'.get_permalink().'">'.get_the_title().'</a></div>';	
						if($has_descrip=='on'){ 
							$excerpt = strip_tags(get_the_content());
							$excerpt = ttruncat($excerpt, $related_descrip_len);
							$output .= '<div class="description">'.$excerpt.'</div>';	
						}
						$output .= bt_format_price($post->ID,$bt_main_options['use_kickstart'],$bt_main_options['use_paypal']);
						$output .= '<div class="pbutton">'.buttoncode(get_the_ID()).'</div>';
						$output .= '<div class="clearme"></div>';
						$output .= '</div>';
	
					endwhile;
					// Reset Post Data
					wp_reset_postdata();			
		} // end foreach 
		$output .= '</div>';
	} // if collections
	return $output;
}

//format price
function bt_format_price($postID, $kickstart='', $paypal='') {
	global $bt_main_options;
	$rawprice = get_post_meta($postID, '_cmb_price', true);
	// strip out non-numerics and add decimals to price
	$price = preg_replace("/[^0-9,.]/", "", $rawprice);
	$price = number_format($price,2);
	$price = '$'.$price;
	// salesprice - used for kickstart only
	$rawsalesprice = get_post_meta($postID, '_cmb_salesprice', true);
	// strip out non-numerics and add decimals to price
	$salesprice = preg_replace("/[^0-9,.]/", "", $rawsalesprice);
	$salesprice = number_format($salesprice,2);
	$salesprice = '$'.$salesprice;

	// if this is kickstart cart, we need to add the price and the sale price
	if( $kickstart=='on' && ( !empty($rawprice) || !empty($rawsalesprice))) {
		if ( !empty($rawprice) && !empty($rawsalesprice) ){
			$theprice .= '<div class="price"><span class="oldprice">'.$price.'</span> <span class="newprice">'.$salesprice.'</span></div>';
		} elseif (!empty($rawprice)) {
			$theprice .= '<div class="price">'.$price.'</div>'; // currently outputting price only for kickstart and paypal
		} else {
			$theprice .= '<div class="price">'.$salesprice.'</div>'; // currently outputting price only for kickstart and paypal
		}
	// if this is paypal and not variable, we need to output the normal price
	} elseif ( !empty($bt_main_options['paypal_email']) && $paypal=='on' && $rawprice>0 ) {
		$theprice .= '<div class="price">'.$price.'</div>';
	}
	
	return $theprice;
}





/*---------------------------------------------------------*/
/* Attach Product Buttons                                  */
/*---------------------------------------------------------*/

function bt_load_product_content() {
	if(is_single()) { 
		add_filter('the_content', 'bt_add_product_content', 12);
	} else {
		add_filter('the_excerpt', 'bt_add_product_content', 14);
	}
}
add_action('wp_head','bt_load_product_content');

function bt_add_product_content($content) {
	global $post, $bt_main_options;

	//only add for the custom post type
	if(get_post_type() != 'min_products'){return $content;}

	// Get the options
	$bt_main_options = get_option("button-store-options_options");  
	$paypalemail = $bt_main_options['paypal_email'];
	$paypal_variations_toggle = get_post_meta($post->ID, '_cmb_paypal_variations_toggle', true);
	
	//excerpt readmore buttons
	if (!empty($bt_main_options['readmorebutton'])){
		$readmorebutton = $bt_main_options['readmorebutton'];		
	} else {
		$readmorebutton = 'More Details &raquo;';
	}

	// excerpt button styles
	// if this is woo, we can add button styles -- 	if ($bt_main_options['is_woo']=='on') {
	// the woo buttons are formed with these classes to the a tag - <a class="woo-sc-button blue"
	// need to add a selector in options that selectst the available colors - silver, blue, red, aqua and a bunch of others.
	
	$sku = get_post_meta($post->ID, '_cmb_sku', true);
	$author = get_post_meta($post->ID, '_cmb_author', true);

	// run function to output the price in accordance with the latest rules 
	$theprice = bt_format_price($post->ID, $bt_main_options['use_kickstart'], $bt_main_options['use_paypal']); 
	$proddetails = '<div id="buttonstore">';

	//price should output according to the rules set in bt_format_price(), currently only show if cart is kickstart or paypal. blocking output if paypal is using variations should also go into that function but for now we check that here
	if(!empty($theprice) && $paypal_variations_toggle != 'on'){ 
		$proddetails .= $theprice;  
	}
	
	if(!empty($author)){
		$proddetails .= '<div class="author">by '.$author.'</div>';
	}
	
	$prodcats = get_the_term_list($post->ID, 'product_category','',', ');
	$prodtags = get_the_term_list($post->ID, 'product_tag','',', ');
	if(!empty($prodcats)){
		$proddetails .= '<div class="prodcats">In Category: '.$prodcats.'</div>';
	}

	// Show for single pages only. Tried to apply conditional to only load this when hook is the_content only... detect if this is firing on the_content using has_filter. (if (has_filter('the_content','add_product_content')==12) { )  ..reveal the following if it is loading
	// as priortiy 12.. just to make it specific. This hides stuff when firing in the excerpt.
	if (!is_archive()) {	
		if (!empty($sku)){
			$proddetails .= '<div class="sku">Product ID: '.$sku.'</div>';
		}
	}

	// archives get the read more link Above the buttons
	if ($bt_main_options['readmore_buttons_placement'] == 'above') { 
		if (is_archive()) {
			$proddetails .= '<div class="readmore"><a class="books-more-top" href="'. esc_url( get_permalink() ) .'">'.$readmorebutton.'</a></div>';
		}
	}

	if(!is_archive()) {	
		if (!empty($bt_main_options['above_button_bar'])) {
			$proddetails .= '<div class="abovebar">'.$bt_main_options['above_button_bar'].'</div>';
		}
	}

	if(!is_archive() || (is_archive() && $bt_main_options['buttons_in_archive'] == 'on')) {
		$proddetails .= bt_get_product_button($post->ID, $theprice); // this is where all buttons are added
	}

	if (!is_archive()) {	
		if (!empty($bt_main_options['below_button_bar'])){ 
			$proddetails .= '<div class="belowbar">'.$bt_main_options['below_button_bar'].'</div>';
		}	
	}

	// Related Products: conditional for showing on excerpt or content
	if((is_archive() and $bt_main_options['related_in_excerpts'] == 'on') or (!is_archive() and $bt_main_options['related_in_content'] == 'on')) {
		$proddetails .= bt_get_related_products($post->ID);
	}

	//archives get the read more link below the buttons
	if($bt_main_options['readmore_buttons_placement'] == 'below'){ 
		if(is_archive()) {	
			$proddetails .= '<div class="readmore"><a class="books-more-bottom" href="'. esc_url( get_permalink() ) .'">'.$readmorebutton.'</a></div>';
		}
	}

	// Show for single pages only. Tried to apply conditional to only load this when hook is the_content only... detect if this is firing on the_content using has_filter. (if (has_filter('the_content','add_product_content')==12) { )  ..reveal the following if it is loading
	// as priortiy 12.. just to make it specific. This hides stuff when firing in the excerpt.
	if(!is_archive()) {
		$descrip = wpautop(get_post_meta($post->ID, '_cmb_additional_description', true));
		if (!empty($descrip)){
			$proddetails .= '<div class="clearme"></div><div class="descrip">'.$descrip.'</div>';
		}
	}

	if(!is_archive()) { // dont show if archive
		if($bt_main_options['use_issuu'] == 'on') { // This needs to be created in main options
			$docID = get_post_meta($post->ID, '_cmb_issue_doc_id', true); // this comes from a book meta code for the book Id from Issuu
			if(!empty($docID)){
				// can also adjust other styles as needed by adding more Issuu related options in book meta
				$proddetails .= '<div class="issuu-box">';
				$proddetails .= '	<h3>Preview</h3>';
				$proddetails .= '	<div>';
				$proddetails .= '		<object style="width:640px;height:472px">';
				$proddetails .= '			<param name="movie" value="http://static.issuu.com/webembed/viewers/style1/v2/IssuuReader.swf?mode=mini&amp;embedBackground=%23000000&amp;backgroundColor=%23222222&amp;documentId='.$docID.'" />';
				$proddetails .= '			<param name="allowfullscreen" value="true"/>';
				$proddetails .= '			<param name="menu" value="false"/>';
				$proddetails .= '			<param name="wmode" value="transparent"/>';
				$proddetails .= '			<embed src="http://static.issuu.com/webembed/viewers/style1/v2/IssuuReader.swf" type="application/x-shockwave-flash" allowfullscreen="true" menu="false" wmode="transparent" style="width:640px;height:472px" flashvars="mode=mini&amp;embedBackground=%23000000&amp;backgroundColor=%23222222&amp;documentId='.$docID.'" />';
				$proddetails .= '		</object>';
				$proddetails .= '	</div>';
				$proddetails .= '</div>';
			} // show only if this has a docID for issuu
		} // show only if main option is set to use issuu
	} // end show for content only	

	$proddetails .= '<div class="clearme"></div>';
	
	$proddetails .= '</div><div id="buttonstorebot"></div>';
	
	return $content.$proddetails;
}

function bt_get_product_button($postID, $theprice = '') {
	global $post, $bt_main_options;
	// get the options from custom-admin-pages options
	$paypalemail = $bt_main_options['paypal_email'];
	$thetitle = get_the_title($post->ID);

	
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
		
	if($bt_main_options['use_amazon']=='on'){
		$buttoncode_ama = get_post_meta($postID, '_cmb_button_code_amazon', true);
	}
	if($bt_main_options['use_cbd']=='on'){
		$buttoncode_cbd = get_post_meta($postID, '_cmb_button_code_cbd', true);
	}
	if($bt_main_options['use_bnn']=='on'){
		$buttoncode_bnn = get_post_meta($postID, '_cmb_button_code_bnn', true);
	}
	if($bt_main_options['use_ejunkie']=='on'){
		$buttoncode_ejunk = get_post_meta($postID, '_cmb_button_code_ejunkie', true);
	}
	if($bt_main_options['use_kickstart']=='on'){
		$buttoncode_kick = get_post_meta($postID, '_cmb_button_code_kickstart', true);
	}

	if($bt_main_options['use_bnn_nook']=='on'){	
		$buttoncode_nook = get_post_meta($postID, '_cmb_button_code_nook', true);
	}
	
	if($bt_main_options['use_kindle']=='on'){	
		$buttoncode_kindle = get_post_meta($postID, '_cmb_button_code_kindle', true);
	}
	if($bt_main_options['use_bim']=='on'){	
		$buttoncode_bim = get_post_meta($postID, '_cmb_button_code_bim', true);
	}
	if($bt_main_options['use_sba']=='on'){	
		$buttoncode_sba = get_post_meta($postID, '_cmb_button_code_sba', true);
	}
	
	if($bt_main_options['use_paypal']=='on'){	
		$buttoncode_paypal = get_post_meta($postID, '_cmb_button_code_paypal', true);
	}	
	
	// start output string
	$buttonoutput = '<div class="prodbutton-row">';

	if (!empty($buttoncode_ama)){
		// detect if an image tag is in the code
		$is_button = bt_find_img_tag($buttoncode_ama); 
			if ($is_button){
				// if so then swap the image
				$ama_button = bt_change_button_image($buttoncode_ama,'amazon');
				$buttonoutput .= '<div class="prodbutton">'.$ama_button .' </div>';
			} else { 
				// if not then add the default image			
		  		$ama_button = bt_add_button_image($buttoncode_ama,'amazon'); 
				$buttonoutput .= '<div class="prodbutton">'.$ama_button .'</div>';
		}
	}
	if (!empty($buttoncode_cbd)){
		// detect if an image tag is in the code
		$is_button = bt_find_img_tag($buttoncode_cbd); 
			if ($is_button){
				// if so then swap the image
				$cbd_button = bt_change_button_image($buttoncode_cbd,'cbd');
				$buttonoutput .= '<div class="prodbutton">'.$cbd_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$cbd_button = bt_add_button_image($buttoncode_cbd,'cbd'); 
				$buttonoutput .= '<div class="prodbutton">'.$cbd_button .'</div>';
		}
	}
	if (!empty($buttoncode_bnn)){
		// detect if an image tag is in the code
		$is_button = bt_find_img_tag($buttoncode_bnn); 
			if ($is_button){
				// if so then swap the image
				$bnn_button = bt_change_button_image($buttoncode_bnn,'bnn');
				$buttonoutput .= '<div class="prodbutton">'.$bnn_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$bnn_button = bt_add_button_image($buttoncode_bnn,'bnn'); 
				$buttonoutput .= '<div class="prodbutton">'.$bnn_button .'</div>';
		}
	}
	if (!empty($buttoncode_ejunk)){
		// detect if an image tag is in the code
		$is_button = bt_find_img_tag($buttoncode_ejunk); 
			if ($is_button){
				// if so then swap the image
				$ejunk_button = bt_change_button_image($buttoncode_ejunk,'ejunkie');
				$buttonoutput .= '<div class="prodbutton">'.$ejunk_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$ejunk_button = bt_add_button_image($buttoncode_ejunk,'ejunkie'); 
				$buttonoutput .= '<div class="prodbutton">'.$ejunk_button .'</div>';
		}
	}
	if (!empty($buttoncode_kick)){
		// detect if an image tag is in the code
		$is_button = bt_find_img_tag($buttoncode_kick); 
			if ($is_button){
				// if so then swap the image
				$kick_button = bt_change_button_image($buttoncode_kick,'kickstart');
				$buttonoutput .= '<div class="prodbutton">'.$kick_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$kick_button = bt_add_button_image($buttoncode_kick,'kickstart'); 
				$buttonoutput .= '<div class="prodbutton">'.$bnn_button .'</div>';
		}
	}
	if (!empty($buttoncode_nook)){
		// detect if an image tag is in the code
		$is_button = bt_find_img_tag($buttoncode_nook); 
			if ($is_button){
				// if so then swap the image
				$nook_button = bt_change_button_image($buttoncode_nook,'nook');
				$buttonoutput .= '<div class="prodbutton">'.$nook_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$nook_button = bt_add_button_image($buttoncode_nook,'nook'); 
				$buttonoutput .= '<div class="prodbutton">'.$nook_button .'</div>';
		}
	}
	
	if (!empty($buttoncode_kindle)){
		// detect if an image tag is in the code
		$is_button = bt_find_img_tag($buttoncode_kindle); 
			if ($is_button){
				// if so then swap the image
				$kindle_button = bt_change_button_image($buttoncode_kindle,'kindle');
				$buttonoutput .= '<div class="prodbutton">'.$kindle_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$kindle_button = bt_add_button_image($buttoncode_kindle,'kindle'); 
				$buttonoutput .= '<div class="prodbutton">'.$kindle_button .'</div>';
		}
	}
	if (!empty($buttoncode_bim)){
		// detect if an image tag is in the code
		$is_button = bt_find_img_tag($buttoncode_bim); 
			if ($is_button){
				// if so then swap the image
				$bim_button = bt_change_button_image($buttoncode_bim,'bim');
				$buttonoutput .= '<div class="prodbutton">'.$bim_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$bim_button = bt_add_button_image($buttoncode_bim,'bim'); 
				$buttonoutput .= '<div class="prodbutton">'.$bim_button .'</div>';
		}
	}
	if (!empty($buttoncode_sba)){
		// detect if an image tag is in the code
		$is_button = bt_find_img_tag($buttoncode_sba); 
			if ($is_button){
				// if so then swap the image
				$sba_button = bt_change_button_image($buttoncode_sba,'sba');
				$buttonoutput .= '<div class="prodbutton">'.$sba_button .'</div>';
			} else { 
				// if not then add the default image			
		  		$sba_button = bt_add_button_image($buttoncode_sba,'sba'); 
				$buttonoutput .= '<div class="prodbutton">'.$sba_button .'</div>';
		}
	}
	
	//If paypal is active, do custom form
	if(!empty($buttoncode_paypal) and !empty($theprice) and !empty($paypalemail))
	{
		$productshome = '/'.get_post_type_object('min_products')->rewrite['slug'].'/';
		// set the return and cancel pages
		if (!empty($bt_main_options['paypal_thankyou_return'])){
				$paypal_thanks = $bt_main_options['paypal_thankyou_return'];
			}else{
				$paypal_thanks = $productshome;
			}
		if (!empty($bt_main_options['paypal_cancel_return'])){
				$paypal_cancel = $bt_main_options['paypal_cancel_return'];
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
			// make theprice usable 
			$numprice = preg_replace("/[^0-9,.]/", "", $theprice);
			$theoptions = '<input type="hidden" name="amount" value="'.$numprice.'">';
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
		$buttonoutput .= '</div>';
	}

	$buttonoutput .= '</div>'; // close prodbutton-row
	return $buttonoutput;
}