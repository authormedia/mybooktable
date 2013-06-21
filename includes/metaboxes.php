<?php

function mbt_init_metaboxes()
{
	add_action('wp_ajax_mbt_buybuttons_metabox', 'mbt_buybuttons_metabox_ajax');
	add_action('wp_ajax_mbt_metadata_metabox', 'mbt_metadata_metabox_ajax');
	add_action('admin_enqueue_scripts', 'mbt_enqueue_metabox_js');

	add_action('save_post', 'mbt_save_metadata_metabox');
	add_action('save_post', 'mbt_save_buybuttons_metabox');
	add_action('save_post', 'mbt_save_series_order_metabox');

	add_action('add_meta_boxes', 'mbt_add_metaboxes', 9);
}
add_action('mbt_init', 'mbt_init_metaboxes');

function mbt_add_metaboxes()
{
	add_meta_box('mbt_blurb', 'Book Blurb', 'mbt_book_blurb_metabox', 'mbt_book', 'normal', 'high');
	add_meta_box('mbt_metadata', 'Book Details', 'mbt_metadata_metabox', 'mbt_book', 'normal', 'high');
	add_meta_box('mbt_buybuttons', 'Buy Buttons', 'mbt_buybuttons_metabox', 'mbt_book', 'normal', 'high');
	add_meta_box('mbt_overview', 'Book Overview', 'mbt_overview_metabox', 'mbt_book', 'normal', 'high');
	add_meta_box('mbt_series_order', 'Series Order', 'mbt_series_order_metabox', 'mbt_book', 'side', 'default');
	add_meta_box('mbt_book_order', 'Book Order', 'mbt_book_order_metabox', 'mbt_book', 'side', 'low');
}

function mbt_enqueue_metabox_js() {
	wp_enqueue_script("mbt-metaboxes", plugins_url('js/metaboxes.js', dirname(__FILE__)));
}



/*---------------------------------------------------------*/
/* Book Blurb Metabox                                      */
/*---------------------------------------------------------*/

function mbt_book_blurb_metabox($post)
{
?>
	<label class="screen-reader-text" for="excerpt">Excerpt</label><textarea rows="1" cols="40" name="excerpt" id="excerpt"><?php echo($post->post_excerpt); ?></textarea>
	<p>Book Blurbs are hand-crafted summaries of your book. The goal of a book blurb is to convince strangers that they need buy your book in 100 words or less. Answer the question "why would I want to read this book?" <a href="<?php echo(admin_url('admin.php?page=mbt_help')); ?>" target="_blank">Learn more about writing your book blurb.</a></p>
<?php
}



/*---------------------------------------------------------*/
/* Overview Metabox                                        */
/*---------------------------------------------------------*/

function mbt_overview_metabox($post)
{
	wp_editor($post->post_content, 'content', array('dfw' => true, 'tabfocus_elements' => 'sample-permalink,post-preview', 'editor_height' => 360) );
	echo('<p>Book Overview is a longer description of your book. This typically includes all the text from the back cover of the book plus, endorsements and any other promotional materials from interior flaps or initial pages. This is also a good place to embed a book trailer if you have one.');
}



/*---------------------------------------------------------*/
/* Metadata Metabox                                        */
/*---------------------------------------------------------*/

function mbt_metadata_metabox_ajax() {
	if($_REQUEST['image_id']) {
		$image = wp_get_attachment_image_src($_REQUEST['image_id'], 'mbt_book_image');
		if($image) {
			list($src, $width, $height) = $image;
		} else {
			$src = apply_filters('mbt_book_placeholder', plugins_url('images/book-placeholder.jpg', dirname(__FILE__)));
		}
		echo('<img src="'.$src.'" class="mbt-book-image">');
	}
	die();
}

function mbt_metadata_metabox($post)
{
?>
	<table class="form-table mbt_metadata_metabox">
		<tr>
			<td rowspan="4">
				<h4 class="mbt-cover-image-title">Book Cover Image</h4>
				<?php mbt_the_book_image(); ?><br>
				<input type="hidden" id="mbt_book_image_id" name="mbt_book_image_id" value="<?php echo(get_post_meta($post->ID, "mbt_book_image_id", true)); ?>" />
				<input id="mbt_set_book_image_button" type="button" class="button" value="Set cover image" />
			</td>
			<th><label for="mbt_unique_id">ISBN</label></th>
			<td>
				<input type="text" name="mbt_unique_id" id="mbt_unique_id" value="<?php echo(get_post_meta($post->ID, "mbt_unique_id", true)); ?>" />
				<p class="description">Unique ID or SKU (optional)</p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_price">Book Price</label></th>
			<td>
				<input type="text" name="mbt_price" id="mbt_price" value="<?php echo(get_post_meta($post->ID, "mbt_price", true)); ?>" />
				<p class="description">(optional)</p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_price">Book Sale Price</label></th>
			<td>
				<input type="text" name="mbt_sale_price" id="mbt_sale_price" value="<?php echo(get_post_meta($post->ID, "mbt_sale_price", true)); ?>" />
				<p class="description">(optional)</p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_price">Sample Chapter</label></th>
			<td>
				<input type="text" id="mbt_sample_url" name="mbt_sample_url" value="<?php echo(get_post_meta($post->ID, "mbt_sample_url", true)); ?>" />
				<input id="mbt_upload_sample_button" type="button" class="button" value="Upload" />
				<p class="description">Upload a sample chapter from your book to give viewers a preview. (optional)</p>
			</td>
		</tr>
	</table>
<?php
}

function mbt_save_metadata_metabox($post_id)
{
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){return;}

	if(get_post_type($post_id) == "mbt_book")
	{
		if(isset($_REQUEST['mbt_book_image_id'])) { update_post_meta($post_id, "mbt_book_image_id", $_REQUEST['mbt_book_image_id']); }
		if(isset($_REQUEST['mbt_unique_id'])) { update_post_meta($post_id, "mbt_unique_id", $_REQUEST['mbt_unique_id']); }
		if(isset($_REQUEST['mbt_price'])) { update_post_meta($post_id, "mbt_price", $_REQUEST['mbt_price']); }
		if(isset($_REQUEST['mbt_sale_price'])) { update_post_meta($post_id, "mbt_sale_price", $_REQUEST['mbt_sale_price']); }
		if(isset($_REQUEST['mbt_sample_url'])) { update_post_meta($post_id, "mbt_sample_url", $_REQUEST['mbt_sample_url']); }
	}
}



/*---------------------------------------------------------*/
/* Buy Button Metabox                                      */
/*---------------------------------------------------------*/

function mbt_buybuttons_metabox_editor($data, $num, $type) {
	$output  = '<div class="mbt_buybutton_editor">';
	$output .= '<div class="mbt_buybutton_editor_header">';
	$output .= '<button class="mbt_buybutton_remover button">Remove</button>';
	$display = empty($data['display']) ? 'featured' : $data['display'];
	$output .= '<div class="mbt_buybutton_display_selector display_'.$display.'" title=""><input type="hidden" name="mbt_buybutton'.$num.'[display]" value="'.$display.'"></div>';
	$output .= '<h4 class="mbt_buybutton_title">'.$type['name'].'</h4>';
	$output .= '</div>';
	$output .= '<div class="mbt_buybutton_editor_fields">';
	$output .= mbt_buybutton_editor($data, "mbt_buybutton".$num, $type);
	$output .= '</div>';
	$output .= '</div>';
	return $output;
}

function mbt_buybuttons_metabox_ajax() {
	$buybuttons = mbt_get_buybuttons();
	if(empty($buybuttons[$_REQUEST['type']])) { die(); }
	echo(mbt_buybuttons_metabox_editor(array('type' => $_REQUEST['type']), $_REQUEST['num'], $buybuttons[$_REQUEST['type']]));
	die();
}

function mbt_buybuttons_metabox($post)
{
	wp_nonce_field(plugin_basename(__FILE__), 'mbt_nonce');

	$buybuttons = mbt_get_buybuttons();
	uasort($buybuttons, create_function('$a,$b', 'return strcasecmp($a["name"],$b["name"]);'));
	echo('Choose One:');
	echo('<select id="mbt_buybutton_selector">');
		echo('<option value=""> -- Choose One -- </option>');
	foreach($buybuttons as $slug => $buybutton) {
		echo('<option value="'.$slug.'">'.$buybutton['name'].'</option>');
	}
	echo('</select>');
	echo('<button id="mbt_buybutton_adder" class="button">Add</button>');

	echo('<div id="mbt_buybutton_editors">');
	$book_buybuttons = mbt_get_book_buybuttons($post->ID);
	if(!empty($book_buybuttons)) {
		for($i = 0; $i < count($book_buybuttons); $i++)
		{
			$button = $book_buybuttons[$i];
			if(empty($buybuttons[$button['type']])) { continue; }
			echo(mbt_buybuttons_metabox_editor($button, $i, $buybuttons[$button['type']]));
		}
	}
	echo('</div>');
}

function mbt_save_buybuttons_metabox($post_id)
{
	if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !isset($_REQUEST['mbt_nonce']) || !wp_verify_nonce($_REQUEST['mbt_nonce'], plugin_basename(__FILE__))){return;}

	if(get_post_type($post_id) == "mbt_book")
	{
		$buybuttons = mbt_get_buybuttons();
		$book_buybuttons = array();
		for($i = 0; isset($_REQUEST['mbt_buybutton'.$i]); $i++)
		{
			$button = $_REQUEST['mbt_buybutton'.$i];
			if(empty($buybuttons[$button['type']])) { continue; }
			$book_buybuttons[] = apply_filters('mbt_'.$button['type'].'_buybutton_save', $button, $buybuttons[$button['type']]);
		}
		update_post_meta($post_id, "mbt_buybuttons", $book_buybuttons);
	}
}



/*---------------------------------------------------------*/
/* Series Order Metabox                                    */
/*---------------------------------------------------------*/

function mbt_series_order_metabox($post) {
?>
	<label for="mbt_series_order">Book Number: </label><input name="mbt_series_order" type="text" size="4" id="mbt_series_order" value="<?php echo(esc_attr(get_post_meta($post->ID, "mbt_series_order", true))); ?>" />
	<p class="mbt-helper-description">Use this to order books within a series.</p>
<?php
}

function mbt_save_series_order_metabox($post_id)
{
	if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !isset($_REQUEST['mbt_nonce']) || !wp_verify_nonce($_REQUEST['mbt_nonce'], plugin_basename(__FILE__))){return;}

	if(get_post_type($post_id) == "mbt_book")
	{
		if(!empty($_REQUEST["mbt_series_order"])) {
			update_post_meta($post_id, "mbt_series_order", $_REQUEST["mbt_series_order"]);
		} else {
			update_post_meta($post_id, "mbt_series_order", 0);
		}
	}
}



/*---------------------------------------------------------*/
/* Book Order Metabox                                      */
/*---------------------------------------------------------*/

function mbt_book_order_metabox($post) {
?>
	<label for="menu_order">Book Order: </label><input name="menu_order" type="text" size="4" id="menu_order" value="<?php echo(esc_attr($post->menu_order)); ?>" />
	<p class="mbt-helper-description">Use this to change the order that books show up on your book table. Books with larger numbers are displayed first.</p>
<?php
}