<?php

function mbt_add_metaboxes()
{
	add_meta_box('mbt_blurb', 'Book Blurb', 'mbt_book_blurb_metabox', 'mbt_book', 'normal', 'high');
	add_meta_box('mbt_overview', 'Book Overview', 'mbt_overview_metabox', 'mbt_book', 'normal', 'high');
	add_meta_box('mbt_metadata', 'Book Metadata', 'mbt_metadata_metabox', 'mbt_book', 'normal', 'high');
	add_meta_box('mbt_buybuttons', 'Buy Buttons', 'mbt_buybuttons_metabox', 'mbt_book', 'normal', 'high');
	add_meta_box('mbt_series_order', 'Series Order', 'mbt_series_order_metabox', 'mbt_book', 'side', 'default');
}
add_action('add_meta_boxes', 'mbt_add_metaboxes', 9);



/*---------------------------------------------------------*/
/* Book Blurb Metabox                                      */
/*---------------------------------------------------------*/

function mbt_book_blurb_metabox($post)
{
?>
	<label class="screen-reader-text" for="excerpt">Excerpt</label><textarea rows="1" cols="40" name="excerpt" id="excerpt"><?php echo($post->post_excerpt); ?></textarea>
	<p>Book Blurbs are hand-crafted summaries of your book. <a href="<?php echo(admin_url('admin.php?page=mbt_help')); ?>" target="_blank">Learn more about writing your book blurb.</a></p>
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

function mbt_include_media_uploader() {
	wp_enqueue_script("bmt_sample_upload", plugins_url('js/sample-upload.js', dirname(__FILE__)));
	wp_enqueue_media();
}
add_action("admin_enqueue_scripts", "mbt_include_media_uploader");

function mbt_metadata_metabox($post)
{
?>
	<table class="form-table mbt_metadata_metabox">
		<tr>
			<th><label for="mbt_unique_id">ISBN</label></th>
			<td>
				<input type="text" name="mbt_unique_id" id="mbt_unique_id" value="<?php echo(get_post_meta($post->ID, "mbt_unique_id", true)); ?>" />
				<p class="description">Unique ID or SKU (optional)</p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_price">Book Price</label></th>
			<td>
				$ <input type="text" name="mbt_price" id="mbt_price" value="<?php echo(get_post_meta($post->ID, "mbt_price", true)); ?>" />
				<p class="description">(optional)</p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_price">Book Sale Price</label></th>
			<td>
				$ <input type="text" name="mbt_sale_price" id="mbt_sale_price" value="<?php echo(get_post_meta($post->ID, "mbt_sale_price", true)); ?>" />
				<p class="description">(optional)</p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_price">Sample Chapter</label></th>
			<td>
				<input type="text" id="mbt_sample_url" name="mbt_sample_url" value="<?php echo(get_post_meta($post->ID, "mbt_sample_url", true)); ?>" />
        		<input id="mbt_upload_sample_button" type="button" class="button" value="Upload" />
				<p class="description">Upload a sample chapter from your book to give viewers a preview.</p>
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
		if(isset($_POST['mbt_unique_id'])) { update_post_meta($post_id, "mbt_unique_id", $_POST['mbt_unique_id']); }
		if(isset($_POST['mbt_price'])) { update_post_meta($post_id, "mbt_price", $_POST['mbt_price']); }
		if(isset($_POST['mbt_sale_price'])) { update_post_meta($post_id, "mbt_sale_price", $_POST['mbt_sale_price']); }
		if(isset($_POST['mbt_sample_url'])) { update_post_meta($post_id, "mbt_sample_url", $_POST['mbt_sample_url']); }
	}
}
add_action('save_post', 'mbt_save_metadata_metabox');



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
	if(empty($buybuttons[$_POST['type']])) { die(); }
	echo(mbt_buybuttons_metabox_editor(array('type' => $_POST['type']), $_POST['num'], $buybuttons[$_POST['type']]));
	die();
}
add_action('wp_ajax_mbt_buybuttons_metabox', 'mbt_buybuttons_metabox_ajax');

function mbt_buybuttons_metabox($post)
{
	wp_nonce_field(plugin_basename(__FILE__), 'mbt_nonce');

	?>

	<script type="text/javascript">
		jQuery(document).ready(function() {
			function reset_numbers() {
				jQuery('#mbt_buybutton_editors .mbt_buybutton_editor').each(function(i) {
					jQuery(this).find("input, textarea, select").each(function() {
						ele = jQuery(this);
						ele.attr('name', ele.attr('name').replace(/mbt_buybutton\d*/, "mbt_buybutton"+i));
						if(ele.attr('id')) { ele.attr('id', ele.attr('id').replace(/mbt_buybutton\d*/, "mbt_buybutton"+i)); }
					});
				});
			}

			jQuery('#mbt_buybutton_adder').click(function(e) {
				if(!jQuery('#mbt_buybutton_selector').val()){return false;}
				jQuery('#mbt_buybutton_selector').attr('disabled', 'disabled');
				jQuery('#mbt_buybutton_adder').attr('disabled', 'disabled');
				jQuery.post(ajaxurl,
					{
						action: 'mbt_buybuttons_metabox',
						type: jQuery('#mbt_buybutton_selector').val(),
						num: 0
					},
					function(response) {
						jQuery('#mbt_buybutton_selector').removeAttr('disabled');
						jQuery('#mbt_buybutton_adder').removeAttr('disabled');
						element = jQuery(response);
						element.find(".mbt_buybutton_display_selector").each(apply_display_title);
						enable_sortability(jQuery(element));
						jQuery("#mbt_buybutton_editors").prepend(element);
						reset_numbers();
					}
				);
				return false;
			});

			jQuery("#mbt_buybutton_editors").on("click", ".mbt_buybutton_remover", function() {
				jQuery(this).parents('.mbt_buybutton_editor').remove();
				reset_numbers();
			});

			function display_description(display) {
				if(display == "book_only") { return "This store will be displayed as a button only on the book page."; }
				if(display == "text_only") { return "This store will be displayed as text underneith the other buttons only on the book page."; }
				if(display == "featured") { return "This store will be displayed as a button on the book listings and the book page."; }
			}
			function apply_display_title() {
				element = jQuery(this)
				element.tooltip();
				element.tooltip("option", "content", display_description(element.find("input").val()));
			}
			jQuery("#mbt_buybutton_editors").on("click", ".mbt_buybutton_display_selector", function() {
				element = jQuery(this);
				input = element.find('input');
				old_display = input.val()
				new_display = old_display == "featured" ? "book_only" : old_display == "book_only" ? "text_only" : "featured";
				input.val(new_display);
				element.removeClass("display_"+old_display);
				element.addClass("display_"+new_display);
				element.tooltip("option", "content", display_description(new_display));
			});
			jQuery(".mbt_buybutton_display_selector").each(apply_display_title);

			function enable_sortability(element) {
				element.sortable({cancel: ".mbt_buybutton_editor_fields", cursor:"move", stop: function(){reset_numbers();}});
				element.find(".mbt_buybutton_editor_header").disableSelection();
			}
			enable_sortability(jQuery("#mbt_buybutton_editors"));
		});
	</script>

	<?php

	$buybuttons = mbt_get_buybuttons();
	ksort($buybuttons);
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
	if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !isset($_POST['mbt_nonce']) || !wp_verify_nonce($_POST['mbt_nonce'], plugin_basename(__FILE__))){return;}

	if(get_post_type($post_id) == "mbt_book")
	{
		$buybuttons = mbt_get_buybuttons();
		$book_buybuttons = array();
		for($i = 0; isset($_POST['mbt_buybutton'.$i]); $i++)
		{
			$button = $_POST['mbt_buybutton'.$i];
			if(empty($buybuttons[$button['type']])) { continue; }
			$book_buybuttons[] = apply_filters('mbt_'.$button['type'].'_buybutton_save', $button, $buybuttons[$button['type']]);
		}
		update_post_meta($post_id, "mbt_buybuttons", $book_buybuttons);
	}
}
add_action('save_post', 'mbt_save_buybuttons_metabox');



/*---------------------------------------------------------*/
/* Series Order Metabox                                    */
/*---------------------------------------------------------*/

function mbt_series_order_metabox($post) {
?>
	<label for="menu_order">Book Number: </label><input name="menu_order" type="text" size="4" id="menu_order" value="<?php echo esc_attr($post->menu_order) ?>" />
<?php
}