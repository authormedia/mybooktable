<?php

/*---------------------------------------------------------*/
/* General SEO Functions                                   */
/*---------------------------------------------------------*/

function mbt_seo_init() {
	if(defined('WPSEO_FILE')) {
		//WP SEO Integration
		add_filter('wpseo_defaults', 'mbt_filter_wpseo_defaults', 10, 2);
		add_filter('wpseo_metadesc', 'mbt_filter_wpseo_metadesc');
		add_filter('wpseo_canonical', 'mbt_filter_wpseo_canonical');
		add_filter('wpseo_title', 'mbt_filter_wpseo_title');
		add_filter('wpseo_opengraph_type', 'mbt_filter_wpseo_opengraph_type');
		add_action('wpseo_opengraph', 'mbt_add_wpseo_opengraph_image', 15);
		add_action('wpseo_opengraph', 'mbt_add_wpseo_opengraph_isbn', 30);
	} else if(mbt_get_setting('enable_seo')) {
		//Custom SEO metabox
		add_action('save_post', 'mbt_save_seo_metabox');
		add_action('add_meta_boxes', 'mbt_add_seo_metabox', 9);

		//Custom SEO overrides
		add_filter('wp_title', 'mbt_seo_wp_title', 999);
		add_filter('woo_title', 'mbt_seo_woo_title', 999, 3);
		add_action('wp_head', 'mbt_seo_add_metadesc');
		add_action('wp_head', 'mbt_seo_add_opengraph');
	}

}
add_action('mbt_init', 'mbt_seo_init');



/*---------------------------------------------------------*/
/* WPSEO Integration Functions                             */
/*---------------------------------------------------------*/

function mbt_filter_wpseo_defaults($defaults, $option_name) {
	if($option_name == 'wpseo_titles') {
		$defaults['title-ptarchive-mbt_book'] = '%%pt_plural%% %%sep%% %%sitename%%';
		$defaults['title-mbt_book'] = '%%title%% by %%ct_mbt_author%%';
		$defaults['metadesc-mbt_book'] = '%%excerpt%%';
		$defaults['title-tax-mbt_author'] = '%%term_title%% %%sep%% %%sitename%%';
		$defaults['metadesc-tax-mbt_author'] = '%%term_description%%';
		$defaults['title-tax-mbt_series'] = '%%term_title%% %%sep%% %%sitename%%';
		$defaults['metadesc-tax-mbt_series'] = '%%term_description%%';
		$defaults['title-tax-mbt_genre'] = '%%term_title%% %%sep%% %%sitename%%';
		$defaults['metadesc-tax-mbt_genre'] = '%%term_description%%';
		$defaults['title-tax-mbt_tag'] = '%%term_title%% %%sep%% %%sitename%%';
		$defaults['metadesc-tax-mbt_tag'] = '%%term_description%%';
	}

	return $defaults;
}

function mbt_filter_wpseo_metadesc($metadesc) {
	global $mbt_archive_query;
	if(mbt_is_archive_query()) {
		$wpseo_front = WPSEO_Frontend::get_instance();
		$term = $mbt_archive_query->get_queried_object();

		if($mbt_archive_query->is_tax()) {
			if(isset($wpseo_front->options['metadesc-tax-'.$term->taxonomy])) {
				$template = $wpseo_front->options['metadesc-tax-'.$term->taxonomy];
				$metadesc = trim(wpseo_replace_vars($template, $term));
			} else {
				$metadesc = mbt_seo_tax_metadesc($mbt_archive_query->get($term->taxonomy), $term->taxonomy);
			}
		} else if($mbt_archive_query->is_post_type_archive('mbt_book')) {
			if(isset($wpseo_front->options['metadesc-ptarchive-'.$term->query_var])) {
				$template = $wpseo_front->options['metadesc-ptarchive-'.$term->query_var];
				$metadesc = trim(wpseo_replace_vars($template, $term));
			}
		}
	}
	return $metadesc;
}

function mbt_filter_wpseo_canonical($canonical) {
	global $mbt_archive_query;
	if(mbt_is_archive_query() and $mbt_archive_query->is_tax()) {
		$term = $mbt_archive_query->get_queried_object();
		$canonical = get_term_link($mbt_archive_query->get($term->taxonomy), $term->taxonomy);
	} else if(mbt_is_archive_query() and $mbt_archive_query->is_post_type_archive('mbt_book')) {
		$canonical = get_post_type_archive_link('mbt_book');
		$canonical = mbt_seo_pagify_link($canonical, $mbt_archive_query->get('paged'));
	}
	return $canonical;
}

function mbt_filter_wpseo_title($title) {
	global $mbt_archive_query;
	if(mbt_is_archive_query()) {
		$wpseo_front = WPSEO_Frontend::get_instance();
		$object = $mbt_archive_query->get_queried_object();

		if($mbt_archive_query->is_tax()) {
			$title = $wpseo_front->get_title_from_options('title-tax-'.$object->taxonomy, $object);
			if(!is_string($title) || '' === $title) {
				$title = mbt_seo_tax_title($mbt_archive_query->get($term->taxonomy), $term->taxonomy);
			}
		} else if($mbt_archive_query->is_post_type_archive('mbt_book')) {
			$new_title = $wpseo_front->get_title_from_options('title-ptarchive-mbt_book');
			if(is_string($new_title) and '' !== $new_title) {
				$title = $new_title;
			}
		}
	}
	return $title;
}

function mbt_filter_wpseo_opengraph_type($type) {
	global $mbt_archive_query;
	if(is_singular('mbt_book')) {
		$type = 'book';
	} else if(mbt_is_archive_query() and $mbt_archive_query->is_tax()) {
		$type = 'object';
	} else if(mbt_is_archive_query() and $mbt_archive_query->is_post_type_archive('mbt_book')) {
		$type = 'object';
	} else if(mbt_is_booktable_page()) {
		$type = 'object';
	}
	return $type;
}

function mbt_add_wpseo_opengraph_image() {
	global $mbt_archive_query, $post;
	if(is_tax('mbt_author') or is_tax('mbt_series') or is_tax('mbt_genre') or is_tax('mbt_tag')) {
		$query_obj = get_queried_object();
		if(!empty($query_obj) and !empty($query_obj->taxonomy)) {
			$image = mbt_get_taxonomy_image($query_obj->taxonomy, $query_obj->term_id);
		}
	} else if(mbt_is_archive_query() and $mbt_archive_query->is_tax()) {
		$query_obj = $mbt_archive_query->get_queried_object();
		if(!empty($query_obj) and !empty($query_obj->taxonomy)) {
			$image = mbt_get_taxonomy_image($query_obj->taxonomy, $query_obj->term_id);
		}
	} else if(is_singular('mbt_book')) {
		$image = mbt_get_book_image_src($post->ID);
		$image = $image[0];
	}

	if(!empty($image)) {
		echo("<meta property='og:image' content='".esc_url($image)."'/>\n");
	}
}

function mbt_add_wpseo_opengraph_isbn() {
	global $post;
	if(is_singular('mbt_book')) {
		$isbn = get_post_meta($post->ID, 'mbt_unique_id', true);
		if(!empty($isbn)) { echo("<meta property='book:isbn' content='".$isbn."'/>\n"); }
	}
}



/*---------------------------------------------------------*/
/* MyBookTable SEO Functions                               */
/*---------------------------------------------------------*/

function mbt_seo_pagify_link($url, $page) {
	if($page > 1) {
		global $wp_rewrite;
		if(!$wp_rewrite->using_permalinks()) {
			$url = add_query_arg('paged', $page, $url);
		} else {
			$url = user_trailingslashit(trailingslashit($url).trailingslashit($wp_rewrite->pagination_base).$page);
		}
	}
	return esc_url($url);
}

function mbt_seo_title($post_id = 0) {
	$title = '';

	$post = get_post($post_id);
	if($post) {
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
			$template_fix = ((get_option('template') === "twentyten" or get_option('template') === "twentyeleven") ? " " : "");
			$title = get_the_title().(empty($authors) ? "" : " by ".$authors).$template_fix;
		} else {
			$title = $seo_title." ";
		}
	}

	return $title;
}

function mbt_seo_tax_title($term = '', $taxonomy = '') {
	$title = '';

	if(empty($term) or empty($taxonomy)) {
		global $mbt_archive_query, $wp_query;
		$query = mbt_is_archive_query() ? $mbt_archive_query : $wp_query;
		if($query->is_tax()) { $term_obj = $query->get_queried_object(); }
	} else {
		$term_obj = get_term_by('slug', $term, $taxonomy);
	}

	if($term_obj and !empty($term_obj->labels)) {
		$title = $term_obj->labels->name." - ".get_bloginfo('name');
	} else if($term_obj and !empty($term_obj->name)) {
		$title = $term_obj->name." - ".get_bloginfo('name');
	}

	return $title;
}

function mbt_seo_metadesc($post_id = 0) {
	$metadesc = '';

	$post = get_post($post_id);
	if($post) {
		$seo_metadesc = get_post_meta($post->ID, 'mbt_seo_metadesc', true);
		if(empty($seo_metadesc)) {
			$metadesc = strip_tags($post->post_excerpt);
		} else {
			$metadesc = $seo_metadesc;
		}
	}

	return htmlentities($metadesc);
}

function mbt_seo_tax_metadesc($term = '', $taxonomy = '') {
	$metadesc = '';

	$term_obj = null;
	if(empty($term) or empty($taxonomy)) {
		global $mbt_archive_query, $wp_query;
		$query = mbt_is_archive_query() ? $mbt_archive_query : $wp_query;
		if($query->is_tax()) { $term_obj = $query->get_queried_object(); }
	} else {
		$term_obj = get_term_by('slug', $term, $taxonomy);
	}
	if($term_obj and !empty($term_obj->description)) {
		$metadesc = $term_obj->description;
	}

	return htmlentities($metadesc);
}

function mbt_seo_archive_title() {
	return mbt_get_product_name()." - ".get_bloginfo('name');
}

function mbt_seo_wp_title($title) {
	global $mbt_archive_query;
	$new_title = '';

	if(is_tax('mbt_author') or is_tax('mbt_series') or is_tax('mbt_genre') or is_tax('mbt_tag') or (mbt_is_archive_query() and $mbt_archive_query->is_tax())) {
		$new_title = mbt_seo_tax_title();
	} else if(is_singular('mbt_book')) {
		$new_title = mbt_seo_title();
	} else if(is_post_type_archive('mbt_book') or (mbt_is_archive_query() and $mbt_archive_query->is_post_type_archive('mbt_book'))) {
		$new_title = mbt_seo_archive_title();
	}
	if(!empty($new_title)) {
		if(substr($title, 0, 7) == "<title>") { $new_title = '<title>'.$new_title.'</title>'; }
		$title = $new_title;
	}

	return $title;
}

function mbt_seo_woo_title($title, $sep, $raw_title) {
	if(is_tax('mbt_author') or is_tax('mbt_series') or is_tax('mbt_genre') or is_tax('mbt_tag') or is_post_type_archive('mbt_book') or is_singular('mbt_book') or mbt_is_archive_query()) {
		return $raw_title;
	}
	return $title;
}

function mbt_seo_add_metadesc() {
	global $mbt_archive_query;
	if(is_tax('mbt_author') or is_tax('mbt_series') or is_tax('mbt_genre') or is_tax('mbt_tag') or (mbt_is_archive_query() and $mbt_archive_query->is_tax())) {
		$metadesc = mbt_seo_tax_metadesc();
		if($metadesc) {
			echo('<meta name="description" content="'.$metadesc.'"/>'."\n");
		}
	} else if(is_singular('mbt_book')) {
		$metadesc = mbt_seo_metadesc();
		if($metadesc) {
			echo('<meta name="description" content="'.$metadesc.'"/>'."\n");
		}
	}
}

function mbt_seo_add_opengraph() {
	global $mbt_archive_query, $post, $wp_query;
	$tags = array();

	if(is_tax('mbt_author') or is_tax('mbt_series') or is_tax('mbt_genre') or is_tax('mbt_tag') or (mbt_is_archive_query() and $mbt_archive_query->is_tax())) {
		$tags['og:type'] = 'object';
		$tags['og:title'] = mbt_seo_tax_title();
		$tags['og:description'] = mbt_seo_tax_metadesc();
		$tags['og:site_name'] = get_bloginfo('name');
		$query = mbt_is_archive_query() ? $mbt_archive_query : $wp_query;
		$term = $query->get_queried_object();
		if(!empty($term) and !empty($term->taxonomy)) {
			$tags['og:url'] = mbt_seo_pagify_link(get_term_link($query->get($term->taxonomy), $term->taxonomy), $query->get('paged'));
			$image = mbt_get_taxonomy_image($term->taxonomy, $term->term_id);
			if(!empty($image)) { $tags['og:image'] = esc_url($image); }
		}
	} else if(is_singular('mbt_book')) {
		$tags['og:type'] = 'book';
		$tags['og:title'] = mbt_seo_title();
		$tags['og:description'] = mbt_seo_metadesc();
		$tags['og:url'] = esc_url(get_permalink());
		$tags['og:site_name'] = get_bloginfo('name');
		$image = mbt_get_book_image_src($post->ID);
		if(!empty($image[0])) { $tags['og:image'] = esc_url($image[0]); }
		$isbn = get_post_meta($post->ID, 'mbt_unique_id', true);
		if(!empty($isbn)) { $tags['book:isbn'] = $isbn; }
	} else if(is_post_type_archive('mbt_book') or (mbt_is_archive_query() and $mbt_archive_query->is_post_type_archive('mbt_book'))) {
		$tags['og:type'] = 'object';
		$tags['og:title'] = mbt_seo_archive_title();
		$tags['og:site_name'] = get_bloginfo('name');
		$query = mbt_is_archive_query() ? $mbt_archive_query : $wp_query;
		$tags['og:url'] = mbt_seo_pagify_link(get_post_type_archive_link('mbt_book'), $query->get('paged'));
	}

	foreach($tags as $tag => $content) {
		echo('<meta property="'.$tag.'" content="'.$content.'"/>'.PHP_EOL);
	}
}



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
					<label for="mbt_seo_title"><?php _e('SEO Title:', 'mybooktable'); ?></label>
				</th>
				<td>
					<input type="text" placeholder="" id="mbt_seo_title" name="mbt_seo_title" value="<?php echo(get_post_meta($post->ID, 'mbt_seo_title', true)); ?>" class="large-text"><br>
					<p><?php _e('Title display in search engines is limited to 70 chars, <span id="mbt_seo_title-length">70</span> chars left.', 'mybooktable'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mbt_seo_metadesc"><?php _e('Meta Description:', 'mybooktable'); ?></label></th>
				<td>
					<textarea class="large-text" rows="3" id="mbt_seo_metadesc" name="mbt_seo_metadesc"><?php echo(get_post_meta($post->ID, 'mbt_seo_metadesc', true)); ?></textarea>
					<p><?php _e('The <code>meta</code> description will be limited to 156 chars, <span id="mbt_seo_metadesc-length">156</span> chars left.', 'mybooktable'); ?></p>
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
		if(isset($_REQUEST['mbt_seo_title'])) { update_post_meta($post_id, "mbt_seo_title", $_REQUEST['mbt_seo_title']); }
		if(isset($_REQUEST['mbt_seo_metadesc'])) { update_post_meta($post_id, "mbt_seo_metadesc", $_REQUEST['mbt_seo_metadesc']); }
	}
}

function mbt_add_seo_metabox()
{
	add_meta_box('mbt_seo', __('SEO Information', 'mybooktable'), 'mbt_seo_metabox', 'mbt_book', 'normal', 'default');
}
