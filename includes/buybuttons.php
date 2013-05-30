<?php

/*---------------------------------------------------------*/
/* General Buy Buttons Functions                           */
/*---------------------------------------------------------*/

function mbt_get_buybuttons() {
	return apply_filters("mbt_buybuttons", array());
}

function mbt_add_basic_buybuttons($buybuttons) {
	$buybuttons['amazon'] = array('name' => 'Amazon', 'search' => 'http://amazon.com/books');
	$buybuttons['kindle'] = array('name' => 'Amazon Kindle', 'search' => 'http://amazon.com/kindle-ebooks');
	return $buybuttons;
}
add_filter('mbt_buybuttons', 'mbt_add_basic_buybuttons');

function mbt_buybutton_editor($data, $id, $type) {
	$output  = '<input name="'.$id.'[type]" type="hidden" value="'.$data['type'].'">';
	$output .= '<textarea name="'.$id.'[url]" cols="80">'.(empty($data['url']) ? '' : htmlspecialchars($data['url'])).'</textarea>';
	$output .= '<p>Paste in the affiliate link URL for this item.'.(empty($type['search']) ? '' : ' <a href="'.$type['search'].'" target="_blank">Search for books on '.$type['name'].'.</a>').' <a href="'.admin_url('admin.php?page=mbt_help').'" target="_blank">Learn more about adding Buy Button links.</a></p>';
	return apply_filters('mbt_'.$data['type'].'_buybutton_editor', apply_filters('mbt_buybutton_editor', $output, $data, $id, $type), $data, $id, $type);
}

function mbt_buybutton_button($data, $type) {
	if(!empty($data['display']) and $data['display'] == 'text_only') {
		$output = empty($data['url']) ? '' : '<li><a href="'.htmlspecialchars($data['url']).'" target="_blank">Buy from '.$type['name'].'</a></li>';
	} else {
		$output = empty($data['url']) ? '' : '<div class="mbt-book-buybutton"><a href="'.htmlspecialchars($data['url']).'" target="_blank"><img src="'.mbt_image_url($data['type'].'_button.png').'" border="0" alt="Buy from '.$type['name'].'"/></a></div>';
	}
	return apply_filters('mbt_'.$data['type'].'_buybutton_button', apply_filters('mbt_buybutton_button', $output, $data, $type), $data, $type);
}

function mbt_get_book_buybuttons($post_id, $query = '') {
	$buybuttons = get_post_meta($post_id, "mbt_buybuttons", true);
	if(!empty($buybuttons) and !empty($query)) {
		foreach($buybuttons as $i=>$button)
		{
			foreach($query as $key=>$value) {
				if(!empty($button[$key]) and !((is_array($value) and in_array($button[$key], $value)) or $button[$key] == $value)) { unset($buybuttons[$i]); continue; }
			}
		}
		$buybuttons = array_values($buybuttons);
	}
	return apply_filters('mbt_get_book_buybuttons', empty($buybuttons) ? array() : $buybuttons);
}



/*---------------------------------------------------------*/
/* Amazon Buy Buttons Functions                            */
/*---------------------------------------------------------*/

function mbt_get_amazon_AISN($url) {
	$matches = array();
	preg_match("/((dp%2F)|(dp\/)|(dp\/product\/)|(\/ASIN\/)|(gp\/product\/)|(exec\/obidos\/tg\/detail\/\-\/)|(asins=))([A-Z0-9]{10})/", $url, $matches);
	return empty($matches) ? '' : $matches[9];
}

function mbt_get_amazon_tld($url) {
	$matches = array();
	preg_match("/amazon\.([a-zA-Z\.]+)/", $url, $matches);
	return empty($matches) ? '' : $matches[1];
}

function mbt_amazon_buybutton_preview() {
	$id = mbt_get_amazon_AISN($_REQUEST['url']);
	echo(empty($id) ? '<span class="error_message">Invalid Amazon code.</span>' : '<span class="success_message">Valid Amazon code.</span>');
	die();
}
add_action('wp_ajax_mbt_amazon_buybutton_preview', 'mbt_amazon_buybutton_preview');

function mbt_amazon_buybutton_editor($editor, $data, $id, $type) {
	$editor = '
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#'.$id.'_url").change(function() {
				jQuery.post(ajaxurl,
					{
						action: "mbt_amazon_buybutton_preview",
						url: jQuery("#'.$id.'_url").val()
					},
					function(response) {
						jQuery("#'.$id.'_preview").html(response);
					}
				);
			});
		});
	</script>';
	$editor .= '<input name="'.$id.'[type]" type="hidden" value="'.$data['type'].'">';
	$editor .= '<b>'.$type['name'].':</b><br><div id="'.$id.'_preview"></div><textarea id="'.$id.'_url" name="'.$id.'[url]" cols="80" rows="5">'.(empty($data['url']) ? '' : htmlspecialchars($data['url'])).'</textarea>';
	$editor .= '<p>Paste in the Amazon affiliate URL or Button code for this item.'.(empty($type['search']) ? '' : ' <a href="'.$type['search'].'" target="_blank">Search for books on '.$type['name'].'.</a>').' <a href="'.admin_url('admin.php?page=mbt_help').'" target="_blank">Learn more about Amazon Affiliate links.</a></p>';
	return $editor;
}
add_action('mbt_amazon_buybutton_editor', 'mbt_amazon_buybutton_editor', 10, 4);
add_action('mbt_kindle_buybutton_editor', 'mbt_amazon_buybutton_editor', 10, 4);

function mbt_amazon_buybutton_button($button, $data, $type) {
	if(!empty($data['url'])) {
		$tld = mbt_get_amazon_tld($data['url']);
		$aisn = mbt_get_amazon_AISN($data['url']);
		$data['url'] = (empty($tld) or empty($aisn)) ? '' : 'http://www.amazon.'.$tld.'/dp/'.$aisn.'?tag=mybooktable-20';
		if($data['display'] == 'text_only') {
			$button = empty($data['url']) ? '' : '<li><a href="'.htmlspecialchars($data['url']).'" target="_blank">Buy from '.$type['name'].'</a></li>';
		} else {
			$button = empty($data['url']) ? '' : '<div class="mbt-book-buybutton"><a href="'.htmlspecialchars($data['url']).'" target="_blank"><img src="'.mbt_image_url($data['type'].'_button.png').'" border="0" alt="Buy from '.$type['name'].'"/></a></div>';
		}
	}
	return $button;
}
add_action('mbt_amazon_buybutton_button', 'mbt_amazon_buybutton_button', 10, 3);
add_action('mbt_kindle_buybutton_button', 'mbt_amazon_buybutton_button', 10, 3);

function mbt_amazon_buybutton_settings_render() {
?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label for="mbt_amazon_buybutton_affiliate_code" style="color: #666">Amazon/Kindle Affiliate Code</label></th>
				<td>
					<input type="text" id="mbt_amazon_buybutton_affiliate_code" disabled="true" value="" class="regular-text">
					<p class="description">
						<a href="http://www.authormedia.com/mybooktable/">Upgrade your MyBookTable</a> to get amazon affiliate settings!
					</p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}
add_action('mbt_buybutton_settings_render', 'mbt_amazon_buybutton_settings_render');
