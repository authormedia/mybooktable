<?php

/*---------------------------------------------------------*/
/* General Buy Buttons Functions                            */
/*---------------------------------------------------------*/

function mbt_get_buybuttons() {
	return apply_filters("mbt_buybutton", array());
}

function mbt_default_buybutton_editor($data, $id, $buybuttons) {
	echo('<input name="'.$id.'[type]" type="hidden" value="'.$data['type'].'">');
	echo('<b>'.$buybuttons[$data['type']]['name'].':</b><br><textarea name="'.$id.'[value]" cols="60" rows="10">'.$data['value'].'</textarea>');
	echo('<p>Paste in the button code or URL for this item. <a href="'.admin_url('edit.php?post_type=mbt_books&page=mbt_help').'" target="_blank">Learn more about adding Buy Button links.</a></p>');
}

function mbt_amazon_buybutton_button($data) {
	$value = $data['value'];
	$affiliatecode = mbt_get_setting('buybutton_amazon_buybutton_code');
	$matches = array();
	preg_match("/((dp%2F)|(dp\/)|(dp\/product\/)|(o\/ASIN\/)|(gp\/product\/)|(exec\/obidos\/tg\/detail\/\-\/)|(asins=))[A-Z0-9]{10}/", $value, $matches);
	if(!empty($matches)) {
		$url = 'http://www.amazon.com/dp/'.$matches[0].'?tag='.(empty($affiliatecode)?'castlemedia-20':$affiliatecode);
	}
	return (empty($value) or empty($matches)) ? '' : '<div class="mbt-book-button"><a href="'.$url.'" target="_blank"><img src="'.plugins_url('images/amazon_button.png', dirname(__FILE__)).'" border="0" alt="Add to Cart"/></a></div>'; 
}

function mbt_add_basic_buybuttons($buybuttons) {
	$buybuttons['amazon'] = array('name' => 'Amazon', 'desc' => 'Amazon.com Product Button', 'editor' => 'mbt_default_buybutton_editor', 'button' => 'mbt_amazon_buybutton_button');
	return $buybuttons;
}
add_filter('mbt_buybutton', 'mbt_add_basic_buybuttons');

function mbt_amazon_buybutton_settings() {
?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label for="mbt_amazon_buybutton_affiliate_code">Amazon Affiliate Code</label></th>
				<td>
					<input type="text" name="mbt_amazon_buybutton_affiliate_code" id="mbt_amazon_buybutton_affiliate_code" value="" class="regular-text">
					<p class="description">Your personal amazon buybutton code.</p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}
add_filter('mbt_buybutton_settings', 'mbt_amazon_buybutton_settings');

function mbt_amazon_buybutton_settings_save() {
	if(isset($_REQUEST['mbt_amazon_buybutton_affiliate_code'])) {
		mbt_update_setting('buybutton_amazon_affiliate_code', $_REQUEST['mbt_amazon_buybutton_affiliate_code']);
	}
}
add_filter('mbt_buybutton_settings_save', 'mbt_amazon_buybutton_settings_save');