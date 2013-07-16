<?php

/*---------------------------------------------------------*/
/* General Buy Buttons Functions                           */
/*---------------------------------------------------------*/

function mbt_buybuttons_init() {
	add_filter('mbt_stores', 'mbt_add_basic_stores');
	mbt_amazon_buybuttons_init();
	mbt_bnn_buybuttons_init();

}
add_action('mbt_init', 'mbt_buybuttons_init');

function mbt_get_stores() {
	return apply_filters("mbt_stores", array());
}

function mbt_add_basic_stores($stores) {
	$stores['amazon'] = array('name' => 'Amazon', 'search' => 'http://amazon.com/books', 'editor_desc' => 'Paste in the Amazon product URL or Button code for this item. <a href="'.admin_url('admin.php?page=mbt_help').'" target="_blank">Learn more about Amazon Affiliate links.</a>');
	$stores['kindle'] = array('name' => 'Amazon Kindle', 'search' => 'http://amazon.com/kindle-ebooks', 'editor_desc' => 'Paste in the Amazon Kindle product URL or Button code for this item. <a href="'.admin_url('admin.php?page=mbt_help').'" target="_blank">Learn more about Amazon Affiliate links.</a>');
	$stores['audible'] = array('name' => 'Audible.com', 'search' => 'http://www.audible.com/search');
	$stores['bnn'] = array('name' => 'Barnes and Noble', 'search' => 'http://www.barnesandnoble.com/s/?store=book', 'editor_desc' => 'Paste in the Barnes &amp; Noble product URL for this item. <a href="'.admin_url('admin.php?page=mbt_help').'" target="_blank">Learn more about Barnes &amp; Noble Affiliate links.</a>');
	$stores['nook'] = array('name' => 'Barnes and Noble Nook', 'search' => 'http://www.barnesandnoble.com/s/?store=ebook', 'editor_desc' => 'Paste in the Barnes &amp; Noble product URL for this item. <a href="'.admin_url('admin.php?page=mbt_help').'" target="_blank">Learn more about Barnes &amp; Noble Affiliate links.</a>');
	$stores['goodreads'] = array('name' => 'GoodReads', 'search' => 'http://www.goodreads.com/search');
	return $stores;
}

function mbt_buybutton_editor($data, $id, $store) {
	$output  = '<input id="'.$id.'_name" name="'.$id.'[store]" type="hidden" value="'.$data['store'].'">';
	$output .= '<textarea id="'.$id.'_url" name="'.$id.'[url]" cols="80">'.(empty($data['url']) ? '' : htmlspecialchars($data['url'])).'</textarea>';
	$output .= '<p>'.(empty($store['editor_desc']) ? 'Paste in the product URL for this item. <a href="'.admin_url('admin.php?page=mbt_help').'" target="_blank">Learn more about adding Buy Button links.</a>' : $store['editor_desc']).(empty($store['search']) ? '' : ' <a href="'.$store['search'].'" target="_blank">Search for books on '.$store['name'].'.</a>').'</p>';
	return apply_filters('mbt_buybutton_editor', $output, $data, $id, $store);
}

function mbt_format_buybutton($data, $store) {
	$data = apply_filters('mbt_filter_buybutton_data', $data, $store);
	if(!empty($data['display']) and $data['display'] == 'text_only') {
		$output = empty($data['url']) ? '' : '<li><a href="'.htmlspecialchars($data['url']).'" target="_blank">Buy from '.$store['name'].'</a></li>';
	} else {
		$output = empty($data['url']) ? '' : '<div class="mbt-book-buybutton"><a href="'.htmlspecialchars($data['url']).'" target="_blank"><img src="'.mbt_image_url($data['store'].'_button.png').'" border="0" alt="Buy from '.$store['name'].'"/></a></div>';
	}
	return apply_filters('mbt_format_buybutton', $output, $data, $store);
}

function mbt_get_buybuttons($post_id, $query = '') {
	$buybuttons = get_post_meta($post_id, "mbt_buybuttons", true);
	if(!empty($buybuttons) and !empty($query)) {
		foreach($buybuttons as $i=>$buybutton)
		{
			foreach($query as $key=>$value) {
				if(!empty($buybutton[$key]) and !((is_array($value) and in_array($buybutton[$key], $value)) or $buybutton[$key] == $value)) { unset($buybuttons[$i]); continue; }
			}
		}
		$buybuttons = array_values($buybuttons);
	}
	return apply_filters('mbt_get_buybuttons', empty($buybuttons) ? array() : $buybuttons, $query);
}



/*---------------------------------------------------------*/
/* Amazon Buy Buttons Functions                            */
/*---------------------------------------------------------*/

function mbt_amazon_buybuttons_init() {
	add_action('wp_ajax_mbt_amazon_buybutton_preview', 'mbt_amazon_buybutton_preview');
	add_action('mbt_buybutton_editor', 'mbt_amazon_buybutton_editor', 10, 4);
	add_filter('mbt_filter_buybutton_data', 'mbt_filter_amazon_buybutton_data', 10, 2);
	add_action('mbt_affiliate_settings_render', 'mbt_amazon_affiliate_settings_render');
}

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
	echo(empty($id) ? '<span class="error_message">Invalid Amazon product link.</span>' : '<span class="success_message">Valid Amazon product link.</span>');
	die();
}

function mbt_amazon_buybutton_editor($editor, $data, $id, $store) {
	if($data['store'] == 'amazon' or $data['store'] == 'kindle') {
		$editor .= '
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery("#'.$id.'_url").before(jQuery("<div id=\"'.$id.'_preview\"></div>")).change(function() {
					element_id = jQuery(this).attr("id");
					element_id = element_id.substring(0, element_id.length - 4);
					jQuery.post(ajaxurl,
						{
							action: "mbt_amazon_buybutton_preview",
							url: jQuery("#"+element_id+"_url").val()
						},
						function(response) {
							jQuery("#"+element_id+"_preview").html(response);
						}
					);
				});
			});
		</script>';
	}
	return $editor;
}

function mbt_filter_amazon_buybutton_data($data, $store) {
	if(($data['store'] == 'amazon' or $data['store'] == 'kindle') and !empty($data['url'])) {
		$tld = mbt_get_amazon_tld($data['url']);
		$aisn = mbt_get_amazon_AISN($data['url']);
		$data['url'] = (empty($tld) or empty($aisn)) ? '' : 'http://www.amazon.'.$tld.'/dp/'.$aisn.'?tag=mybooktable-20';
	}
	return $data;
}

function mbt_amazon_affiliate_settings_render() {
?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label for="mbt_amazon_buybutton_affiliate_code" style="color: #666">Amazon/Kindle Affiliate Code</label></th>
				<td>
					<input type="text" id="mbt_amazon_buybutton_affiliate_code" disabled="true" value="" class="regular-text">
					<p class="description">
						<?php
						if(mbt_get_setting('dev_active') and !defined('MBTDEV_VERSION')) {
							echo('<a href="https://www.authormedia.com/my-account/" target="_blank">Download the MyBookTable Developer Add-on to activate your advanced features!</a>');
						} else if(mbt_get_setting('pro_active') and !mbt_get_setting('dev_active') and !defined('MBTPRO_VERSION')) {
							echo('<a href="https://www.authormedia.com/my-account/" target="_blank">Download the MyBookTable Professional Add-on to activate your advanced features!</a>');
						} else if(!mbt_get_setting('pro_active') and (defined('MBTPRO_VERSION') or defined('MBTDEV_VERSION'))) {
							echo('<a href="'.admin_url('admin.php?page=mbt_settings&setup_api_key=1').'" target="_blank">Insert your API Key</a> to activate your advanced features!');
						} else {
							echo('<a href="http://www.authormedia.com/mybooktable/add-ons" target="_blank">Upgrade your MyBookTable</a> to get Amazon affiliate integration!');
						}
						?>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}

/*---------------------------------------------------------*/
/* Barnes & Noble Buy Buttons Functions                    */
/*---------------------------------------------------------*/

function mbt_bnn_buybuttons_init() {
	add_action('wp_ajax_mbt_bnn_buybutton_preview', 'mbt_bnn_buybutton_preview');
	add_action('mbt_buybutton_editor', 'mbt_bnn_buybutton_editor', 10, 4);
	add_action('mbt_filter_buybutton_data', 'mbt_filter_bnn_buybutton_data', 10, 2);
	add_action('mbt_affiliate_settings_render', 'mbt_linkshare_affiliate_settings_render');
}

function mbt_get_bnn_identifier($url) {
	$matches = array();
	preg_match("/barnesandnoble.com\/w\/([0-9a-z\-]+\/[0-9]{10})/", $url, $matches);
	$description = empty($matches) ? '' : $matches[1];
	preg_match("/[eE][aA][nN]=([0-9]{13})/", $url, $matches);
	$ean = empty($matches) ? '' : $matches[1];
	return (empty($description) or empty($ean)) ? '' : $description.'?ean='.$ean;
}

function mbt_bnn_buybutton_preview() {
	$id = mbt_get_bnn_identifier($_REQUEST['url']);
	echo(empty($id) ? '<span class="error_message">Invalid Barnes &amp; Noble product link.</span>' : '<span class="success_message">Valid Barnes &amp; Noble product link.</span>');
	die();
}

function mbt_bnn_buybutton_editor($editor, $data, $id, $store) {
	if($data['store'] == 'bnn' or $data['store'] == 'nook') {
		$editor .= '
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery("#'.$id.'_url").before(jQuery("<div id=\"'.$id.'_preview\"></div>")).change(function() {
					element_id = jQuery(this).attr("id");
					element_id = element_id.substring(0, element_id.length - 4);
					jQuery.post(ajaxurl,
						{
							action: "mbt_bnn_buybutton_preview",
							url: jQuery("#"+element_id+"_url").val()
						},
						function(response) {
							jQuery("#"+element_id+"_preview").html(response);
						}
					);
				});
			});
		</script>';
	}
	return $editor;
}

function mbt_filter_bnn_buybutton_data($data, $store) {
	if(($data['store'] == 'bnn' or $data['store'] == 'nook') and !empty($data['url'])) {
		$book_id = mbt_get_bnn_identifier($data['url']);
		$data['url'] = empty($book_id) ? '' : 'http://www.barnesandnoble.com/w/'.$book_id.'&cm_mmc=AFFILIATES-_-Linkshare-_-W1PQs9y/1/c-_-10:1';
	}
	return $data;
}

function mbt_linkshare_affiliate_settings_render() {
?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label for="mbt_linkshare_web_services_token" style="color: #666">LinkShare Web Services Token<br>(Used for Barnes &amp; Noble Affiliates)</label></th>
				<td>
					<input type="text" id="mbt_linkshare_web_services_token" disabled="true" value="" class="regular-text">
					<p class="description">
						<?php
						if(mbt_get_setting('dev_active') and !defined('MBTDEV_VERSION')) {
							echo('<a href="https://www.authormedia.com/my-account/" target="_blank">Download the MyBookTable Developer Add-on to activate your advanced features!</a>');
						} else if(mbt_get_setting('pro_active') and !mbt_get_setting('dev_active') and !defined('MBTPRO_VERSION')) {
							echo('<a href="https://www.authormedia.com/my-account/" target="_blank">Download the MyBookTable Professional Add-on to activate your advanced features!</a>');
						} else if(!mbt_get_setting('pro_active') and (defined('MBTPRO_VERSION') or defined('MBTDEV_VERSION'))) {
							echo('<a href="'.admin_url('admin.php?page=mbt_settings&setup_api_key=1').'" target="_blank">Insert your API Key</a> to activate your advanced features!');
						} else {
							echo('<a href="http://www.authormedia.com/mybooktable/add-ons" target="_blank">Upgrade your MyBookTable</a> to get Linkshare affiliate integration!');
						}
						?>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}
