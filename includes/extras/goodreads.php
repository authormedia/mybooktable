<?php

function mbt_goodreads_init() {
	add_action('mbt_general_settings_render', 'mbt_goodreads_settings_render');
	add_action('mbt_settings_save', 'mbt_goodreads_settings_save');
	add_action('mbt_after_single_book', 'mbt_the_goodreads_reviews');
}
add_action('mbt_init', 'mbt_goodreads_init');

function mbt_goodreads_settings_save() {
	if(isset($_REQUEST['mbt_goodreads_developer_key'])) {
		mbt_update_setting('goodreads_developer_key', $_REQUEST['mbt_goodreads_developer_key']);
	}
}

function mbt_goodreads_settings_render() {
?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label for="mbt_goodreads_developer_key"><?php _e('GoodReads Developer Key', 'mybooktable'); ?></label></th>
				<td>
					<input type="text" id="mbt_goodreads_developer_key" name="mbt_goodreads_developer_key" value="<?php echo(mbt_get_setting('goodreads_developer_key')); ?>" class="regular-text">
					<p class="description"><?php _e('Insert your GoodReads Developer Key to enable GoodReads reviews on your book pages. <a href="http://www.authormedia.com/how-to-add-goodreads-book-reviews-to-mybooktable/" target="_blank">Learn how to get a GoodReads Developer Key', 'mybooktable'); ?></a></p>
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