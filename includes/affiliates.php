<?php

/*---------------------------------------------------------*/
/* General Affiliates Functions                            */
/*---------------------------------------------------------*/

function mbt_get_affiliates() {
	return apply_filters("mbt_affiliate", array());
}

function mbt_default_affiliate_editor($data, $id, $affiliates) {
	echo('<input name="'.$id.'[type]" type="hidden" value="'.$data['type'].'">');
	echo('<b>'.$affiliates[$data['type']]['name'].':</b><br><textarea name="'.$id.'[value]" cols="60" rows="10">'.$data['value'].'</textarea>');
	echo('<p>'.$affiliates[$data['type']]['desc'].'</p>');
}

//check if button code contains image tag
function mbt_find_img_tag($buttoncode){ return preg_match("/<img[^<]*>/i", $buttoncode); }

//format to change button code
function mbt_change_button_image($buttoncode, $type) {
	$newbutton = '<img src="'.plugins_url('images/'.$type.'_button.png', dirname(__FILE__)).'" border="0" alt="Add to Cart"/>';
	$output = preg_replace("/<img[^<]*>/i", $newbutton, $buttoncode, 1);
	return empty($output) ? $buttoncode : $output;
}

//add button image to the link code
function mbt_add_button_image($buttoncode, $type) {
	// check to see that this has a good chance of being a normal url
	if(substr($buttoncode, 0, 4) == 'http') {
		return '<a href="'.$buttoncode.'" target="_blank"><img src="'.plugins_url('images/'.$type.'_button.png', dirname(__FILE__)).'" border="0" alt="Add to Cart"/></a>';
	} else {
		return $buttoncode;
	}
}

function mbt_default_affiliate_button($data) {
	$value = $data['value'];
	$type = $data['type'];
	return empty($value) ? '' : '<div class="mbt-book-button">'.(mbt_find_img_tag($value) ? mbt_change_button_image($value, $type) : mbt_add_button_image($value, $type)).' </div>'; 
}

function mbt_add_basic_affiliates($affiliates) {
	$affiliates['amazon'] = array('name' => 'Amazon', 'desc' => 'Paste in the button code or URL for this item. <a href="'.admin_url('edit.php?post_type=mbt_books&page=mbt_help').'" target="_blank">Learn more about adding affiliate links.</a>', 'editor' => 'mbt_default_affiliate_editor', 'button' => 'mbt_default_affiliate_button');
	//$affiliates['cbd'] = array('name' => 'Christian Book Distributors', 'desc' => '(optional) paste in the button code for this item', 'editor' => 'mbt_default_affiliate_editor', 'button' => 'mbt_default_affiliate_button');
	//$affiliates['bnn'] = array('name' => 'Barnes & Noble', 'desc' => '(optional) paste in the button code for this item', 'editor' => 'mbt_default_affiliate_editor', 'button' => 'mbt_default_affiliate_button');
	//$affiliates['ejunkie'] = array('name' => 'eJunkie', 'desc' => '(optional) paste in the button code for this item', 'editor' => 'mbt_default_affiliate_editor', 'button' => 'mbt_default_affiliate_button');
	//$affiliates['kickstart'] = array('name' => 'Kickstart', 'desc' => '(optional) paste in the button code for this item', 'editor' => 'mbt_default_affiliate_editor', 'button' => 'mbt_default_affiliate_button');
	//$affiliates['nook'] = array('name' => 'Nook', 'desc' => '(optional) paste in the button code for this item', 'editor' => 'mbt_default_affiliate_editor', 'button' => 'mbt_default_affiliate_button');
	//$affiliates['kindle'] = array('name' => 'Kindle', 'desc' => '(optional) paste in the button code for this item', 'editor' => 'mbt_default_affiliate_editor', 'button' => 'mbt_default_affiliate_button');
	//$affiliates['bim'] = array('name' => 'Books In Motion', 'desc' => '(optional) paste in the button code for this item', 'editor' => 'mbt_default_affiliate_editor', 'button' => 'mbt_default_affiliate_button');
	//$affiliates['sba'] = array('name' => 'Signed by Author', 'desc' => '(optional) paste in the button code for this item', 'editor' => 'mbt_default_affiliate_editor', 'button' => 'mbt_default_affiliate_button');
	//$affiliates['paypal'] = array('name' => 'Paypal', 'desc' => '(optional) paste in the button code for this item', 'editor' => 'mbt_default_affiliate_editor', 'button' => 'mbt_default_affiliate_button');
	return $affiliates;
}
add_filter('mbt_affiliate', 'mbt_add_basic_affiliates');
