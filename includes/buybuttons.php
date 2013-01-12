<?php

/*---------------------------------------------------------*/
/* General Buy Buttons Functions                           */
/*---------------------------------------------------------*/

function mbt_get_buybuttons() {
	$buybuttons = apply_filters("mbt_buybuttons", array());
	ksort($buybuttons);
	return $buybuttons;
}

function mbt_default_buybutton_editor($data, $id, $buybuttons) {
	echo('<input name="'.$id.'[type]" type="hidden" value="'.$data['type'].'">');
	echo('<b>'.$buybuttons[$data['type']]['name'].':</b><br><textarea name="'.$id.'[value]" cols="80">'.$data['value'].'</textarea>');
	echo('<p>Paste in the affiliate link URL for this item. <a href="'.admin_url('admin.php?page=mbt_help').'" target="_blank">Learn more about adding Buy Button links.</a></p>');
}

function mbt_add_basic_buybuttons($buybuttons) {
	$buybuttons['amazon'] = array('name' => 'Amazon', 'desc' => 'Amazon.com Buy Button', 'editor' => 'mbt_amazon_buybutton_editor', 'button' => 'mbt_amazon_buybutton_button');
	$buybuttons['audible'] = array('name' => 'Audible.com', 'desc' => 'Audible.com Buy Button', 'editor' => 'mbt_default_buybutton_editor', 'button' => 'mbt_audible_buybutton_button');
	$buybuttons['bnn'] = array('name' => 'Barnes and Noble', 'desc' => 'Barnes and Noble Buy Button', 'editor' => 'mbt_default_buybutton_editor', 'button' => 'mbt_bnn_buybutton_button');
	$buybuttons['custom'] = array('name' => 'Custom Buy Button', 'desc' => 'Custom Buy Button', 'editor' => 'mbt_custom_buybutton_editor', 'button' => 'mbt_custom_buybutton_button');
	return $buybuttons;
}
add_filter('mbt_buybuttons', 'mbt_add_basic_buybuttons');

function mbt_custom_buybutton_editor($data, $id, $buybuttons) {
	echo('<input name="'.$id.'[type]" type="hidden" value="'.$data['type'].'">');
	echo('<b>'.$buybuttons[$data['type']]['name'].':</b><br>');
	echo('Button Text: <input type="text" name="'.$id.'[text]" value="'.(isset($data['text'])?$data['text']:'').'"><br>');
	echo('Button Link: <input type="text" name="'.$id.'[value]" value="'.$data['value'].'">');
	echo('<p>Paste in the affiliate link URL for this item. <a href="'.admin_url('admin.php?page=mbt_help').'" target="_blank">Learn more about adding Buy Button links.</a></p>');
}

function mbt_custom_buybutton_button($data) {
	return empty($data['value']) ? '' : '<div class="mbt-book-button"><a class="mbt-custom-button" href="'.$data['value'].'" target="_blank">'.$data['text'].'</a></div>';
}

function mbt_audible_buybutton_button($data) {
	return empty($data['value']) ? '' : '<div class="mbt-book-button"><a href="'.$data['value'].'" target="_blank"><img src="'.plugins_url('images/audible_button.png', dirname(__FILE__)).'" border="0" alt="Buy from Audible.com"/></a></div>';
}

function mbt_bnn_buybutton_button($data) {
	return empty($data['value']) ? '' : '<div class="mbt-book-button"><a href="'.$data['value'].'" target="_blank"><img src="'.plugins_url('images/bnn_button.png', dirname(__FILE__)).'" border="0" alt="Buy from Barnes and Noble"/></a></div>';
}


/*---------------------------------------------------------*/
/* Amazon Buy Buttons Functions                            */
/*---------------------------------------------------------*/

function mbt_get_amazon_AISN($value) {
	$matches = array();
	preg_match("/((dp%2F)|(dp\/)|(dp\/product\/)|(o\/ASIN\/)|(gp\/product\/)|(exec\/obidos\/tg\/detail\/\-\/)|(asins=))([A-Z0-9]{10})/", $value, $matches);
	return empty($matches) ? '' : $matches[9];
}

function mbt_get_amazon_product_request($id) {
	$access_key = '';
	$secret_key = '';

	$parameters = array(
		'Service' => 'AWSECommerceService',
		'AWSAccessKeyId' => $access_key,
		'AssociateTag' => 'mybooktable-20',
		'Timestamp' => gmdate("Y-m-d\TH:i:s\Z"),
		'Version' => '2009-03-01',
		'Operation' => 'ItemLookup',
		'IdType' => 'ASIN',
		'ItemId' => $id,
		'ResponseGroup' => 'Small,Images'
	);

	ksort($parameters);

	$query_vars = array();
	foreach ($parameters as $parameter => $value) {
		$parameter = str_replace("%7E", "~", rawurlencode($parameter));
		$value = str_replace("%7E", "~", rawurlencode($value));
		$query_vars[] = $parameter.'='.$value;
	}

	$query = implode('&', $query_vars);
	$signature = urlencode(base64_encode(hash_hmac('sha256', "GET\nwebservices.amazon.com\n/onca/xml\n".$query, $secret_key, true)));
	return 'http://webservices.amazon.com/onca/xml?'.$query.'&Signature='.$signature;
}

function mbt_get_amazon_product_preview($id) {
	$response = wp_remote_get(mbt_get_amazon_product_request($id));
	if(is_wp_error($response)) { return '<span class="error_message">Error sending request to verify code.</span>'; }

	$xml = new SimpleXMLElement($response['body']);
	if($xml) {
		if($xml->Error) {
			return '<span class="error_message">Amazon API Error:<br>'.$xml->Error->Code.':<br>'.$xml->Error->Message.'</span>';
		}
		if($xml->Items) {
			if($xml->Items->Request) {
				if($xml->Items->Request->Errors) {
					return '<span class="error_message">Amazon API Error:<br>'.$xml->Items->Request->Errors->Error->Code.':<br>'.$xml->Items->Request->Errors->Error->Message.'</span>';
				}
				if($xml->Items->Item) {
					if($xml->Items->Item->SmallImage) {
						return '<img src="'.$xml->Items->Item->SmallImage->URL.'">';
					}
				}
			}
		}
	}
	return '<span class="error_message">Amazon API Response Error.</span>';
}

function mbt_amazon_buybutton_preview() {
	$id = mbt_get_amazon_AISN($_POST['value']);
	//$product = empty($id) ? '' : mbt_get_amazon_product_preview($id);
	echo(empty($id) ? '<span class="error_message">Invalid Amazon code.</span>' : '<span class="success_message">Valid Amazon code.</span>');
	die();
}
add_action('wp_ajax_mbt_amazon_buybutton_preview', 'mbt_amazon_buybutton_preview');

function mbt_amazon_buybutton_editor($data, $id, $buybuttons) {
	?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#<?php echo($id); ?>_value').change(function(){
					jQuery.post(ajaxurl,
						{
							action: 'mbt_amazon_buybutton_preview',
							value: jQuery('#<?php echo($id); ?>_value').val()
						},
						function(response) {
							jQuery('#<?php echo($id); ?>_preview').html(response);
						}
					);
				});
			});
		</script>
	<?php
	echo('<input name="'.$id.'[type]" type="hidden" value="'.$data['type'].'">');
	echo('<b>'.$buybuttons[$data['type']]['name'].':</b><br><div id="'.$id.'_preview"></div><textarea id="'.$id.'_value" name="'.$id.'[value]" cols="80" rows="5">'.$data['value'].'</textarea>');
	echo('<p>Paste in the Amazon affiliate URL or Button code for this item. <a href="'.admin_url('admin.php?page=mbt_help').'" target="_blank">Learn more about Amazon Affiliate links.</a></p>');
}

function mbt_amazon_buybutton_button($data) {
	$id = mbt_get_amazon_AISN($data['value']);
	$affiliatecode = mbt_get_setting('buybutton_amazon_affiliate_code');
	return empty($id) ? '' : '<div class="mbt-book-button"><a href="http://www.amazon.com/dp/'.$id.'?tag='.(empty($affiliatecode)?'mybooktable-20':$affiliatecode).'" target="_blank"><img src="'.plugins_url('images/amazon_button.png', dirname(__FILE__)).'" border="0" alt="Buy from Amazon"/></a></div>';
}

function mbt_amazon_buybutton_settings() {
?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label for="mbt_amazon_buybutton_affiliate_code">Amazon Affiliate Code</label></th>
				<td>
					<input type="text" name="mbt_amazon_buybutton_affiliate_code" id="mbt_amazon_buybutton_affiliate_code" value="<?php echo(mbt_get_setting('buybutton_amazon_affiliate_code')); ?>" class="regular-text">
					<p class="description">
						You can find your amazon affiliate tracking ID by visiting the your <a href="https://affiliate-program.amazon.com/gp/associates/network/main.html" target="_blank">Amazon Affiliate Homepage</a>. The code should be near the top left of the screen and will end in "-20" if you live in the United States of America.
					</p>
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