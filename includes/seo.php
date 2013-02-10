<?php

/*---------------------------------------------------------*/
/* General SEO Functions                                   */
/*---------------------------------------------------------*/

function mbt_is_seo_active() {
	$active = !mbt_get_setting('disable_seo');
	if(defined('WPSEO_FILE')) { $active = false; }
	return apply_filters('mbt_is_seo_active', $active);
}



/*---------------------------------------------------------*/
/* WPSEO Integration Functions                             */
/*---------------------------------------------------------*/

function mbt_filter_wpseo_options($options) {
	if(!isset($options['title-mbt_book']) or empty($options['title-mbt_book'])) {
		$options['title-mbt_book'] = '%%title%% by %%ct_mbt_author%%';
		$options['metadesc-mbt_book'] = '%%excerpt%%';
	}
	if(!isset($options['title-mbt_author']) or empty($options['title-mbt_author'])) {
		$options['title-mbt_author'] = '%%term_title%% | %%sitename%%';
		$options['metadesc-mbt_author'] = '%%term_description%%';
	}
	if(!isset($options['title-mbt_series']) or empty($options['title-mbt_series'])) {
		$options['title-mbt_series'] = '%%term_title%% | %%sitename%%';
		$options['metadesc-mbt_series'] = '%%term_description%%';
	}
	if(!isset($options['title-mbt_genre']) or empty($options['title-mbt_genre'])) {
		$options['title-mbt_genre'] = '%%term_title%% | %%sitename%%';
		$options['metadesc-mbt_genre'] = '%%term_description%%';
	}

	return $options;
}
add_filter('option_wpseo_titles', 'mbt_filter_wpseo_options');

function mbt_force_filter_wpseo_options($options) {
	$options['title-mbt_book'] = '%%title%% by %%ct_mbt_author%%';
	$options['metadesc-mbt_book'] = '%%excerpt_only%%';
	$options['title-mbt_author'] = '%%term_title%% | %%sitename%%';
	$options['metadesc-mbt_author'] = '%%term_description%%';
	$options['title-mbt_series'] = '%%term_title%% | %%sitename%%';
	$options['metadesc-mbt_series'] = '%%term_description%%';
	$options['title-mbt_genre'] = '%%term_title%% | %%sitename%%';
	$options['metadesc-mbt_genre'] = '%%term_description%%';

	return $options;
}

function mbt_reset_wpseo_defaults() {
	update_option('wpseo_titles', mbt_force_filter_wpseo_options(get_option('wpseo_titles')));
}

function mbt_detect_wpseo_reset($input) {
	if(isset($_GET['wpseo_reset_defaults'])) {
		mbt_reset_wpseo_defaults();
	}
	return $input;
}
add_filter('wp_redirect', 'mbt_detect_wpseo_reset', 50);
add_action('activate_wordpress-seo/wp-seo.php', 'mbt_reset_wpseo_defaults');



/*---------------------------------------------------------*/
/* MyBookTable SEO Functions                               */
/*---------------------------------------------------------*/

//override page title
function mbt_seo_wp_title($title) {
	if(mbt_is_seo_active() and is_singular('mbt_book')) {
		global $post;
		$seo_title = get_post_meta($post->ID, 'mbt_seo_title', true);
		if(empty($seo_title)) {
			$terms = get_the_terms($post->ID, "mbt_author");
			$authors = '';
			if($terms) {
				foreach($terms as $term) {
					$authors .= $term->name . ', ';
				}
				$authors = rtrim(trim($authors), ',');
			}
			$title = get_the_title().(empty($authors) ? "" : " by ".$authors)." ";
		} else {
			$title = $seo_title." ";
		}
	}
	return $title;
}
add_filter('wp_title', 'mbt_seo_wp_title', 999);

//add page meta
function mbt_seo_add_metadesc() {
	if(mbt_is_seo_active() and is_singular('mbt_book')) {
		global $post;
		$seo_metadesc = get_post_meta($post->ID, 'mbt_seo_metadesc', true);
		if(empty($seo_metadesc)) {
			echo('<meta name="description" content="'.strip_tags(get_the_excerpt()).'"/>');
		} else {
			echo('<meta name="description" content="'.$seo_metadesc.'"/>');
		}
	}
}
add_action('wp_head', 'mbt_seo_add_metadesc');




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

	if(get_post_type($post_id) == "mbt_book")
	{
		if(isset($_POST['mbt_seo_title'])) { update_post_meta($post_id, "mbt_seo_title", $_POST['mbt_seo_title']); }
		if(isset($_POST['mbt_seo_metadesc'])) { update_post_meta($post_id, "mbt_seo_metadesc", $_POST['mbt_seo_metadesc']); }
	}
}
add_action('save_post', 'mbt_save_seo_metabox');

function mbt_add_seo_metabox()
{
	if(mbt_is_seo_active()) { add_meta_box('mbt_seo', 'SEO Information', 'mbt_seo_metabox', 'mbt_book', 'normal', 'high'); }
}
add_action('add_meta_boxes', 'mbt_add_seo_metabox', 9);