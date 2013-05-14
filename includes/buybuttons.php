<?php

/*---------------------------------------------------------*/
/* General Buy Buttons Functions                           */
/*---------------------------------------------------------*/

function mbt_get_buybuttons() {
	return apply_filters("mbt_buybuttons", array());
}

function mbt_add_basic_buybuttons($buybuttons) {
	$buybuttons['amazon'] = array('name' => 'Amazon', 'desc' => 'Amazon.com Buy Button', 'editor' => 'mbt_amazon_buybutton_editor', 'button' => 'mbt_amazon_buybutton_button');
	$buybuttons['kindle'] = array('name' => 'Amazon Kindle', 'desc' => 'Amazon Kindle Buy Button', 'editor' => 'mbt_amazon_buybutton_editor', 'button' => 'mbt_amazon_buybutton_button');
	return $buybuttons;
}
add_filter('mbt_buybuttons', 'mbt_add_basic_buybuttons');

function mbt_default_buybutton_editor($data, $id, $type) {
	echo('<input name="'.$id.'[type]" type="hidden" value="'.$data['type'].'">');
	echo('<b>'.$type['name'].':</b><br><textarea name="'.$id.'[value]" cols="80">'.$data['value'].'</textarea>');
	//echo('<input name="'.$id.'[text_only]" type="checkbox" '.(!empty($data['text_only']) ? ' checked="checked"' : '').'> Show text only?');
	echo('<p>Paste in the affiliate link URL for this item. <a href="'.admin_url('admin.php?page=mbt_help').'" target="_blank">Learn more about adding Buy Button links.</a></p>');
}

function mbt_default_buybutton_button($data, $type) {
	return apply_filters('mbt_'.$data['type'].'_buybutton', empty($data['value']) ? '' : '<div class="mbt-book-buybutton"><a href="'.$data['value'].'" target="_blank"><img src="'.mbt_image_url($data['type'].'_button.png').'" border="0" alt="Buy from '.$type['name'].'"/></a></div>');
}



/*---------------------------------------------------------*/
/* Amazon Buy Buttons Functions                            */
/*---------------------------------------------------------*/

function mbt_get_amazon_AISN($value) {
	$matches = array();
	preg_match("/((dp%2F)|(dp\/)|(dp\/product\/)|(o\/ASIN\/)|(gp\/product\/)|(exec\/obidos\/tg\/detail\/\-\/)|(asins=))([A-Z0-9]{10})/", $value, $matches);
	return empty($matches) ? '' : $matches[9];
}

function mbt_get_amazon_tld($value) {
	$matches = array();
	preg_match("/amazon\.([a-zA-Z\.]+)/", $value, $matches);
	return empty($matches) ? '' : $matches[1];
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

function mbt_amazon_buybutton_editor($data, $id, $type) {
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
	echo('<b>'.$type['name'].':</b><br><div id="'.$id.'_preview"></div><textarea id="'.$id.'_value" name="'.$id.'[value]" cols="80" rows="5">'.$data['value'].'</textarea>');
	echo('<p>Paste in the Amazon affiliate URL or Button code for this item. <a href="'.admin_url('admin.php?page=mbt_help').'" target="_blank">Learn more about Amazon Affiliate links.</a></p>');
}

function mbt_amazon_buybutton_button($data, $type) {
	$id = mbt_get_amazon_AISN($data['value']);
	$tld = mbt_get_amazon_tld($data['value']);
	$img = mbt_image_url($data['type'].'_button.png');
	$output = empty($id) ? '' : '<div class="mbt-book-buybutton"><a href="http://www.amazon.'.$tld.'/dp/'.$id.'?tag=mybooktable-20" target="_blank"><img src="'.$img.'" border="0" alt="Buy from '.$type['name'].'"/></a></div>';
	return apply_filters('mbt_'.$data['type'].'_buybutton', $output);
}



/*---------------------------------------------------------*/
/* Styles                                                  */
/*---------------------------------------------------------*/

function mbt_image_url($image) {
	$style = mbt_get_setting('buybutton_style');
	if(empty($style)) { $style = 'Default'; }

	$url = mbt_styled_image_url($image, $style);
	if(empty($url)) { $url = mbt_styled_image_url($image, 'Default'); }
	if(empty($url)) { $url = plugins_url('styles/Default/'.$image, dirname(__FILE__)); }

	return apply_filters('mbt_image_url_'.$image, $url);
}

function mbt_styled_image_url($image, $style) {
	$directories = mbt_get_style_directories();

	foreach($directories as $directory) {
		if(file_exists($directory['dir'].'/'.$style)) {
			if(file_exists($directory['dir'].'/'.$style.'/'.$image)) {
				return $directory['url'].'/'.$style.'/'.$image;
			}
		}
	}

	return '';
}

function mbt_get_buybutton_styles() {
	$directories = mbt_get_style_directories();
	$styles = array();

	foreach($directories as $directory) {
		if($handle = opendir($directory['dir'])) {
			while(false !== ($folder = readdir($handle))) {
				if ($folder != '.' and $folder != '..' and $folder != 'Default' and !in_array($folder, $styles)) {
					$styles[] = $folder;
				}
			}
			closedir($handle);
		}
	}

	return $styles;
}

function mbt_get_style_directories() {
	return apply_filters('mbt_style_directories', array());
}

function mbt_add_default_style_directory($directories) {
	$directories[] = array('dir' => plugin_dir_path(dirname(__FILE__)).'styles', 'url' => plugins_url('styles', dirname(__FILE__)));
	return $directories;
}
add_filter('mbt_style_directories', 'mbt_add_default_style_directory', 100);