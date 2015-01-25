<?php

function mbt_goodreads_init() {
	add_action('mbt_integrate_settings_render', 'mbt_goodreads_settings_render');
	add_action('mbt_settings_save', 'mbt_goodreads_settings_save');
	add_action('wp_ajax_mbt_goodreads_developer_key_refresh', 'mbt_goodreads_developer_key_refresh_ajax');
	add_filter('mbt_reviews_boxes', 'mbt_add_goodreads_reviews_box');
}
add_action('mbt_init', 'mbt_goodreads_init');

function mbt_add_goodreads_reviews_box($reviews) {
	$dev_key = mbt_get_setting('goodreads_developer_key');
	$disabled = empty($dev_key) ? '<a href="'.admin_url('admin.php?page=mbt_settings&mbt_current_tab=5').'">'.__('You must input your GoodReads Developer Key', 'mybooktable').'</a>' : '';
	$reviews['goodreads'] = array(
		'name' => 'GoodReads Reviews',
		'callback' => 'mbt_the_goodreads_reviews',
		'disabled' => $disabled,
	);
	return $reviews;
}

function mbt_goodreads_settings_save() {
	if(isset($_REQUEST['mbt_goodreads_developer_key'])) {
		mbt_update_setting('goodreads_developer_key', $_REQUEST['mbt_goodreads_developer_key']);
	}
}

if(!function_exists('mbt_goodreads_developer_key_refresh_ajax')) {
	function mbt_goodreads_developer_key_refresh_ajax() {
		mbt_update_setting('goodreads_developer_key', $_REQUEST['data']);
		echo(mbt_goodreads_developer_key_feedback());
		die();
	}
}

if(!function_exists('mbt_goodreads_developer_key_feedback')) {
	function mbt_goodreads_developer_key_feedback() {
		$output = '';
		$goodreads_developer_key = mbt_get_setting("goodreads_developer_key");
		if(!empty($goodreads_developer_key)) {
			$raw_response = wp_remote_get('http://www.goodreads.com/book/isbn?format=json&isbn=9780618640157&key='.$goodreads_developer_key);
			if(!is_wp_error($raw_response) and 200 == wp_remote_retrieve_response_code($raw_response)) { $response = json_decode(wp_remote_retrieve_body($raw_response)); }
			if(!empty($response->reviews_widget)) {
				$output .= '<span class="mbt_admin_message_success">'.__('Valid Developer Key', 'mybooktable').'</span>';
			} else {
				$output .= '<span class="mbt_admin_message_failure">'.__('Invalid Developer Key', 'mybooktable').'</span>';
			}
		}
		return $output;
	}
}

function mbt_goodreads_settings_render() {
?>
	<table class="form-table">
		<tbody>
			<tr>
				<th><label for="mbt_goodreads_developer_key"><?php _e('GoodReads Developer Key', 'mybooktable'); ?></label></th>
				<td>
					<div class="mbt_api_key_feedback mbt_feedback"></div>
					<input type="text" id="mbt_goodreads_developer_key" name="mbt_goodreads_developer_key" value="<?php echo(mbt_get_setting('goodreads_developer_key')); ?>" class="regular-text">
					<div class="mbt_feedback_refresh mbt_feedback_refresh_initial" data-refresh-action="mbt_goodreads_developer_key_refresh" data-element="mbt_goodreads_developer_key"></div>
					<p class="description"><?php _e('Insert your GoodReads Developer Key to enable GoodReads reviews on your book pages.', 'mybooktable') ?> <a href="http://www.authormedia.com/how-to-add-goodreads-book-reviews-to-mybooktable/" target="_blank"> <?php _e('Learn how to get a GoodReads Developer Key', 'mybooktable'); ?></a></p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}

function mbt_get_goodreads_reviews($post_id) {
	global $wp_version;

	$output = '';
	$key = mbt_get_setting('goodreads_developer_key');
	$isbn = get_post_meta($post_id, 'mbt_unique_id', true);
	if(!empty($key) and !empty($isbn)) {
		$raw_response = wp_remote_get('http://www.goodreads.com/book/isbn?format=json&isbn='.$isbn.'&key='.$key, array('timeout' => 3, 'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url')));
		if(!is_wp_error($raw_response) and 200 == wp_remote_retrieve_response_code($raw_response)) {
			$response = json_decode(wp_remote_retrieve_body($raw_response));
			$output = empty($response->reviews_widget) ? '' : $response->reviews_widget;
			$output = preg_replace("/<style>.*<\/style>/s", "", $output);
		}
	}
	return $output;
}
function mbt_the_goodreads_reviews() {
	global $post;
	echo(mbt_get_goodreads_reviews($post->ID));
}