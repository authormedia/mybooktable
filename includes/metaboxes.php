<?php

function bss_add_metaboxes()
{
	add_meta_box('mbt_blurb', 'Book Blurb', 'mbt_book_blurb_metabox', 'mbt_books', 'normal', 'high');
	add_meta_box('mbt_overview', 'Book Overview', 'mbt_overview_metabox', 'mbt_books', 'normal', 'high');
	add_meta_box('mbt_metadata', 'Book Metadata', 'mbt_metadata_metabox', 'mbt_books', 'normal', 'high');
	if(mbt_is_seo_active()) { add_meta_box('mbt_seo', 'SEO Information', 'mbt_seo_metabox', 'mbt_books', 'normal', 'high'); }
	add_meta_box('mbt_buybuttons', 'Buy Buttons', 'mbt_buybuttons_metabox', 'mbt_books', 'normal');
}
add_action('add_meta_boxes', 'bss_add_metaboxes', 9);


/*---------------------------------------------------------*/
/* Book Blurb Metabox                                      */
/*---------------------------------------------------------*/

function mbt_book_blurb_metabox($post)
{
?>
	<label class="screen-reader-text" for="excerpt">Excerpt</label><textarea rows="1" cols="40" name="excerpt" id="excerpt"><?php echo($post->post_excerpt); ?></textarea>
	<p>Book Blurbs are hand-crafted summaries of your book. <a href="<?php echo(admin_url('edit.php?post_type=mbt_books&page=mbt_help')); ?>" target="_blank">Learn more about writing your book blurb.</a></p>
<?php
}

/*---------------------------------------------------------*/
/* Overview Metabox                                        */
/*---------------------------------------------------------*/

function mbt_overview_metabox($post)
{
	wp_editor($post->post_content, 'content', array('dfw' => true, 'tabfocus_elements' => 'sample-permalink,post-preview', 'editor_height' => 360) );
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
			<th><label for="mbt_book_id">Book ID</label></th>
			<td>
				<input type="text" name="mbt_book_id" id="mbt_book_id" value="<?php echo(get_post_meta($post->ID, "mbt_book_id", true)); ?>" />
				<p class="description">SKU or Unique ID</p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_price">Book Price</label></th>
			<td>
				$ <input type="text" name="mbt_price" id="mbt_price" value="<?php echo(get_post_meta($post->ID, "mbt_price", true)); ?>" />
				<p class="description">Optional</p>
			</td>
		</tr>
		<tr>
			<th><label for="mbt_price">Book Sale Price</label></th>
			<td>
				$ <input type="text" name="mbt_sale_price" id="mbt_sale_price" value="<?php echo(get_post_meta($post->ID, "mbt_sale_price", true)); ?>" />
				<p class="description">Optional</p>
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

	if(get_post_type($post_id) == "mbt_books")
	{
		if(isset($_POST['mbt_book_id'])) { update_post_meta($post_id, "mbt_book_id", $_POST['mbt_book_id']); }
		if(isset($_POST['mbt_price'])) { update_post_meta($post_id, "mbt_price", $_POST['mbt_price']); }
		if(isset($_POST['mbt_sale_price'])) { update_post_meta($post_id, "mbt_sale_price", $_POST['mbt_sale_price']); }
		if(isset($_POST['mbt_sample_url'])) { update_post_meta($post_id, "mbt_sample_url", $_POST['mbt_sample_url']); }
	}
}
add_action('save_post', 'mbt_save_metadata_metabox');

/*---------------------------------------------------------*/
/* SEO Metabox                                             */
/*---------------------------------------------------------*/

function mbt_seo_metabox($post)
{
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			update_title = function() {
				left = 70-jQuery("#mbt_seo_title").val().length;
				jQuery("#mbt_seo_title-length").text(left);
				if(left < 0) {
					jQuery("#mbt_seo_title-length").addClass("bad");
				} else {
					jQuery("#mbt_seo_title-length").removeClass("bad");
				}
			}
			jQuery("#mbt_seo_title").keydown(update_title).keyup(update_title).change(update_title);
			update_title();

			update_metadesc = function() {
				left = 156-jQuery("#mbt_seo_metadesc").val().length;
				jQuery("#mbt_seo_metadesc-length").text(left);
				if(left < 0) {
					jQuery("#mbt_seo_metadesc-length").addClass("bad");
				} else {
					jQuery("#mbt_seo_metadesc-length").removeClass("bad");
				}
			}
			jQuery("#mbt_seo_metadesc").keydown(update_metadesc).keyup(update_metadesc).change(update_metadesc);
			update_metadesc();
		});
	</script>

	<table class="form-table mbt_seo_metabox">
		<tbody>
			<tr>
				<th scope="row">
					<label for="mbt_seo_title">SEO Title:</label>
				</th>
				<td>
					<input type="text" placeholder="" id="mbt_seo_title" name="mbt_seo_title" value="<?php echo(get_post_meta($post->ID, 'mbt_seo_title', true)); ?>" class="large-text"><br>
					<p>Title display in search engines is limited to 70 chars, <span id="mbt_seo_title-length">70</span> chars left.</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mbt_seo_metadesc">Meta Description:</label></th>
				<td>
					<textarea class="large-text" rows="3" id="mbt_seo_metadesc" name="mbt_seo_metadesc"><?php echo(get_post_meta($post->ID, 'mbt_seo_metadesc', true)); ?></textarea>
					<p>The <code>meta</code> description will be limited to 156 chars, <span id="mbt_seo_metadesc-length">156</span> chars left.</p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}

function mbt_save_seo_metabox($post_id)
{
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){return;}

	if(get_post_type($post_id) == "mbt_books")
	{
		if(isset($_POST['mbt_seo_title'])) { update_post_meta($post_id, "mbt_seo_title", $_POST['mbt_seo_title']); }
		if(isset($_POST['mbt_seo_metadesc'])) { update_post_meta($post_id, "mbt_seo_metadesc", $_POST['mbt_seo_metadesc']); }
	}
}
add_action('save_post', 'mbt_save_seo_metabox');

/*---------------------------------------------------------*/
/* Buy Button Metabox                                      */
/*---------------------------------------------------------*/

function mbt_buybuttons_metabox_ajax() {
	echo('<div class="mbt_buybutton_editor">');
	echo('<button class="mbt_buybutton_remover" style="float:right">Remove</button>');
	$buybuttons = mbt_get_buybuttons();
	echo($buybuttons[$_POST['type']]['editor'](array('type' => $_POST['type'], 'value' => ''), "mbt_buybutton".$_POST['num'], $buybuttons));
	echo('</div>');
	die();
}
add_action('wp_ajax_mbt_buybuttons_metabox', 'mbt_buybuttons_metabox_ajax');

function mbt_buybuttons_metabox($post)
{
	wp_nonce_field(plugin_basename(__FILE__), 'mbt_nonce');

	?>

	<script type="text/javascript">
		jQuery(document).ready(function() {
			var adding = false;

			function reset_numbers() {
				jQuery('#mbt_buybutton_editors .mbt_buybutton_editor').each(function(i) {
					jQuery(this).find("input, textarea, select").each(function() {
						jQuery(this).attr('name', jQuery(this).attr('name').replace(/mbt_buybutton\d*\[([A-Za-z0-9]*)\]/, "mbt_buybutton"+i+"[$1]"));
					});
				});
			}

			jQuery('#mbt_buybutton_adder').click(function() {
				if(!adding) {
					adding = true;
					jQuery.post(ajaxurl,
						{
							action: 'mbt_buybuttons_metabox',
							type: jQuery('#mbt_buybutton_selector').val(),
							num: 0
						},
						function(response) {
							var element = jQuery(response);
							jQuery("#mbt_buybutton_editors").prepend(element);
							reset_numbers();
							adding = false;
						}
					);
				}
				return false;
			});

			jQuery("#mbt_buybutton_editors").on("click", ".mbt_buybutton_remover", function() {
				jQuery(this).parent().remove();
				reset_numbers();
			});
		});
	</script>

	<?php

	$buybuttons = mbt_get_buybuttons();
	echo('Choose One:');
	echo('<select id="mbt_buybutton_selector">');
	foreach($buybuttons as $slug => $buybutton) {
  		echo('<option value="'.$slug.'">'.$buybutton['name'].'</option>');
  	}
	echo('</select>');
	echo('<button id="mbt_buybutton_adder">Add</button>');

	echo('<div id="mbt_buybutton_editors">');
	$post_buybuttons = get_post_meta($post->ID, "mbt_buybuttons", true);
	if(!empty($post_buybuttons)) {
		for($i = 0; $i < count($post_buybuttons); $i++)
		{
			echo('<div class="mbt_buybutton_editor">');
			echo('<button class="mbt_buybutton_remover" style="float:right">Remove</button>');
			echo($buybuttons[$post_buybuttons[$i]['type']]['editor']($post_buybuttons[$i], "mbt_buybutton".$i, $buybuttons));
			echo('</div>');
		}
	}
	echo('</div>');
}

function mbt_save_buybuttons_metabox($post_id)
{
	if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !isset($_POST['mbt_nonce']) || !wp_verify_nonce($_POST['mbt_nonce'], plugin_basename(__FILE__))){return;}

	if(get_post_type($post_id) == "mbt_books")
	{
		$mydata = array();
		for($i = 0; isset($_POST['mbt_buybutton'.$i]); $i++)
		{
			$mydata[] = $_POST['mbt_buybutton'.$i];
		}
		update_post_meta($post_id, "mbt_buybuttons", $mydata);
	}
}
add_action('save_post', 'mbt_save_buybuttons_metabox');
