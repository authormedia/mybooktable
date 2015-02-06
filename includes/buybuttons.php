<?php

/*---------------------------------------------------------*/
/* General Buy Buttons Functions                           */
/*---------------------------------------------------------*/

function mbt_buybuttons_init() {
	add_filter('mbt_stores', 'mbt_add_basic_stores');
	mbt_amazon_buybuttons_init();
	mbt_bnn_buybuttons_init();
	mbt_kobo_buybuttons_init();
	mbt_cj_affiliates_init();
}
add_action('mbt_init', 'mbt_buybuttons_init');

function mbt_get_stores() {
	return apply_filters("mbt_stores", array());
}

function mbt_add_basic_stores($stores) {
	if(mbt_get_setting('enable_default_affiliates') or mbt_get_upgrade()) {
		$stores['amazon'] = array('name' => 'Amazon', 'search' => 'http://amazon.com/books');
		$stores['kindle'] = array('name' => 'Amazon Kindle', 'search' => 'http://amazon.com/kindle-ebooks');
		$stores['bnn'] = array('name' => 'Barnes and Noble', 'search' => 'http://www.barnesandnoble.com/s/?store=book');
		$stores['nook'] = array('name' => 'Barnes and Noble Nook', 'search' => 'http://www.barnesandnoble.com/s/?store=ebook');
		$stores['audible'] = array('name' => 'Audible.com', 'search' => 'http://www.audible.com/search');
		$stores['kobo'] = array('name' => 'Kobo', 'search' => 'http://www.kobobooks.com');
	}
	$stores['goodreads'] = array('name' => 'GoodReads', 'search' => 'http://www.goodreads.com/search');
	$stores['cbd'] = array('name' => 'Christian Book Distributor', 'search' => 'http://www.christianbook.com/Christian/Books/easy_find');
	$stores['sba'] = array('name' => 'Signed by the Author', 'search' => 'http://www.signedbytheauthor.com');
	$stores['bam'] = array('name' => 'Books-A-Million', 'search' => 'http://www.booksamillion.com/search');
	$stores['bookbaby'] = array('name' => 'BookBaby');
	$stores['lifeway'] = array('name' => 'Lifeway', 'search' => 'http://www.lifeway.com');
	$stores['mardel'] = array('name' => 'Mardel', 'search' => 'http://www.mardel.com/search');
	$stores['smashwords'] = array('name' => 'Smashwords', 'search' => 'http://www.smashwords.com');
	$stores['indiebound'] = array('name' => 'Indie Bound', 'search' => 'http://www.indiebound.org');
	$stores['createspace'] = array('name' => 'CreateSpace', 'search' => 'https://www.createspace.com');
	$stores['alibris'] = array('name' => 'Alibris', 'search' => 'http://www.alibris.com');
	$stores['bookdepository'] = array('name' => 'Book Depository', 'search' => 'http://www.bookdepository.com');
	$stores['ibooks'] = array('name' => 'iBooks', 'search' => 'http://www.researchmaniacs.com/Search/iBookstore.html');
	$stores['powells'] = array('name' => 'Powells', 'search' => 'http://www.powells.com');
	$stores['scribd'] = array('name' => 'Scribd', 'search' => 'http://www.scribd.com');
	$stores['sony'] = array('name' => 'Sony Reader', 'search' => 'https://ebookstore.sony.com');
	$stores['googleplay'] = array('name' => 'Google Play', 'search' => 'https://play.google.com');
	$stores['gumroad'] = array('name' => 'Gumroad');
	return $stores;
}

function mbt_buybutton_editor($data, $id, $store) {
	$output  = '<input id="'.$id.'_name" name="'.$id.'[store]" type="hidden" value="'.$data['store'].'">';
	$output .= '<textarea id="'.$id.'_url" name="'.$id.'[url]" cols="80">'.(empty($data['url']) ? '' : htmlspecialchars($data['url'])).'</textarea>';
	$editor_desc = (empty($store['editor_desc']) ? __('Paste in the product URL for this item.', 'mybooktable').' <a href="'.admin_url('admin.php?page=mbt_help&mbt_video_tutorial=buy_buttons').'" target="_blank">'.__('Learn more about adding Buy Button links.', 'mybooktable').'</a>' : $store['editor_desc']);
	$editor_search = (empty($store['search']) ? '' : ' <a href="'.$store['search'].'" target="_blank">'.sprintf(__('Search for books on %s.', 'mybooktable'), $store['name']).'</a>');
	$output .= '<p>'.$editor_desc.$editor_search.'</p>';
	return apply_filters('mbt_buybutton_editor', $output, $data, $id, $store);
}

function mbt_format_buybutton($data, $store) {
	$data = apply_filters('mbt_filter_buybutton_data', $data, $store);
	if(!empty($data['display']) and $data['display'] == 'text_only') {
		$output = empty($data['url']) ? '' : '<li><a href="'.htmlspecialchars($data['url']).'" target="_blank" rel="nofollow">'.sprintf(__('Buy from %s', 'mybooktable'), $store['name']).'</a></li>';
	} else {
		$output = empty($data['url']) ? '' : '<div class="mbt-book-buybutton"><a href="'.htmlspecialchars($data['url']).'" target="_blank" rel="nofollow"><img src="'.mbt_image_url($data['store'].'_button.png').'" border="0" alt="'.sprintf(__('Buy from %s.', 'mybooktable'), $store['name']).'"/></a></div>';
	}
	return apply_filters('mbt_format_buybutton', $output, $data, $store);
}

function mbt_query_buybuttons($post_id, $query = '') {
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
	return apply_filters('mbt_query_buybuttons', empty($buybuttons) ? array() : $buybuttons, $query);
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
	echo(empty($id) ? '<span class="error_message">'.__('Invalid Amazon product link.', 'mybooktable').'</span>' : '<span class="success_message">'.__('Valid Amazon product link.', 'mybooktable').'</span>');
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
		$data['url'] = (empty($tld) or empty($aisn)) ? '' : 'http://www.amazon.'.$tld.'/dp/'.$aisn.'?tag=ammbt-20';
	}
	return $data;
}

function mbt_amazon_affiliate_settings_render() {
?>
	<table class="form-table">
		<tbody>
			<tr>
				<th>
					<label style="color: #666"><?php _e('Amazon Associates', 'mybooktable'); ?></label>
					<div class="mbt-affiliate-usedby">
						Used by:
						<ul>
							<li>Amazon Buy Button</li>
							<li>Kindle Buy Button</li>
						</ul>
					</div>
				</th>
				<td>
					<input type="text" disabled="true" value="" class="regular-text">
					<p class="description"><?php echo(mbt_get_upgrade_message()); ?></p>
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

function mbt_is_bbn_url_valid($url) {
	return preg_match("/barnesandnoble.com\/((s\/([0-9]{13}))|(w\/.*[eE][aA][nN]=([0-9]{13})))/", $url);
}

function mbt_get_bnn_identifier($url) { return ' '; }

function mbt_bnn_buybutton_preview() {
	echo(!mbt_is_bbn_url_valid($_REQUEST['url']) ? '<span class="error_message">'.__('Invalid Barnes &amp; Noble product link.', 'mybooktable').'</span>' : '<span class="success_message">'.__('Valid Barnes &amp; Noble product link.', 'mybooktable').'</span>');
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
		$data['url'] = !mbt_is_bbn_url_valid($data['url']) ? '' : 'http://click.linksynergy.com/deeplink?id=W1PQs9y/1/c&mid=36889&murl='.urlencode($data['url']);
	}
	return $data;
}

function mbt_linkshare_affiliate_settings_render() {
?>
	<table class="form-table">
		<tbody>
			<tr>
				<th>
					<label style="color: #666"><?php _e('LinkShare', 'mybooktable'); ?></label>
					<div class="mbt-affiliate-usedby">
						Used by:
						<ul>
							<li>Barnes &amp; Noble Buy Button</li>
							<li>Nook Buy Button</li>
							<li>Kobo Buy Button</li>
						</ul>
					</div>
				</th>
				<td>
					<input type="text" disabled="true" value="" class="regular-text">
					<p class="description"><?php echo(mbt_get_upgrade_message()); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}



/*---------------------------------------------------------*/
/* Kobo Buy Button Functions                               */
/*---------------------------------------------------------*/

function mbt_kobo_buybuttons_init() {
	add_action('mbt_filter_buybutton_data', 'mbt_filter_kobo_buybutton_data', 10, 2);
}

function mbt_filter_kobo_buybutton_data($data, $store) {
	return $data;
}



/*---------------------------------------------------------*/
/* Commission Junction Functions                           */
/*---------------------------------------------------------*/

function mbt_cj_affiliates_init() {
	add_action('mbt_filter_buybutton_data', 'mbt_filter_audible_buybutton_data', 10, 2);
	add_action('mbt_affiliate_settings_render', 'mbt_cj_affiliate_settings_render');
}

function mbt_get_cj_affiliate_link($url, $website_id) {
	$server = 'www.qksrv.net';

	$scheme = 'http';
	$url_info = parse_url($url);
	if(!empty($url_info) and !empty($url_info['scheme'])) { $scheme = $url_info['scheme']; }

	$hashIndex = strpos($url, '#');
	$frag = "";
	if($hashIndex !== false) {
		$frag = substr($url, $hashIndex + 1);
		$url = substr($url, 0, $hashIndex);
	}

	$extraParams = "";
	if(!empty($frag)) { $extraParams = "/fragment/".urlencode($frag); }

	return $scheme."://".$server."/links/".$website_id."/type/am".$extraParams."/".$url;
}

function mbt_filter_audible_buybutton_data($data, $store) {
	if($data['store'] == 'audible' and !empty($data['url'])) {
		$data['url'] = mbt_get_cj_affiliate_link($data['url'], 7737731);
	}
	return $data;
}

function mbt_cj_affiliate_settings_render() {
?>
	<table class="form-table">
		<tbody>
			<tr>
				<th>
					<label style="color: #666"><?php _e('Commission Junction', 'mybooktable'); ?></label>
					<div class="mbt-affiliate-usedby">
						Used by:
						<ul>
							<li>Audible.com Buy Button</li>
						</ul>
					</div>
				</th>
				<td>
					<input type="text" disabled="true" value="" class="regular-text">
					<p class="description"><?php echo(mbt_get_upgrade_message()); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}
