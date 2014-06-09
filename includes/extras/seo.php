<?php

/*---------------------------------------------------------*/
/* General SEO Functions                                   */
/*---------------------------------------------------------*/

function mbt_seo_init() {
	if(defined('WPSEO_FILE')) {
		//WP SEO Integration
		add_filter('option_wpseo_titles', 'mbt_filter_wpseo_options');
		add_filter('wp_redirect', 'mbt_detect_wpseo_reset', 50);
		add_action('activate_wordpress-seo/wp-seo.php', 'mbt_reset_wpseo_defaults');
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
		add_action('wp_head', 'mbt_seo_add_metadesc');
		add_action('wp_head', 'mbt_seo_add_opengraph');
	}

}
add_action('mbt_init', 'mbt_seo_init');



/*---------------------------------------------------------*/
/* WPSEO Integration Functions                             */
/*---------------------------------------------------------*/

function mbt_filter_wpseo_options($options) {
	if(empty($options['title-mbt_book'])) {
		$options['title-mbt_book'] = '%%title%% by %%ct_mbt_author%%';
		$options['metadesc-mbt_book'] = '%%excerpt%%';
	}
	if(empty($options['title-mbt_author'])) {
		$options['title-mbt_author'] = '%%term_title%% | %%sitename%%';
		$options['metadesc-mbt_author'] = '%%term_description%%';
	}
	if(empty($options['title-mbt_series'])) {
		$options['title-mbt_series'] = '%%term_title%% | %%sitename%%';
		$options['metadesc-mbt_series'] = '%%term_description%%';
	}
	if(empty($options['title-mbt_genre'])) {
		$options['title-mbt_genre'] = '%%term_title%% | %%sitename%%';
		$options['metadesc-mbt_genre'] = '%%term_description%%';
	}
	if(empty($options['title-mbt_tag'])) {
		$options['title-mbt_tag'] = '%%term_title%% | %%sitename%%';
		$options['metadesc-mbt_tag'] = '%%term_description%%';
	}

	return $options;
}

function mbt_force_filter_wpseo_options($options) {
	$options['title-mbt_book'] = '%%title%% by %%ct_mbt_author%%';
	$options['metadesc-mbt_book'] = '%%excerpt_only%%';
	$options['title-mbt_author'] = '%%term_title%% | %%sitename%%';
	$options['metadesc-mbt_author'] = '%%term_description%%';
	$options['title-mbt_series'] = '%%term_title%% | %%sitename%%';
	$options['metadesc-mbt_series'] = '%%term_description%%';
	$options['title-mbt_genre'] = '%%term_title%% | %%sitename%%';
	$options['metadesc-mbt_genre'] = '%%term_description%%';
	$options['title-mbt_tag'] = '%%term_title%% | %%sitename%%';
	$options['metadesc-mbt_tag'] = '%%term_description%%';

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

function mbt_filter_wpseo_metadesc($metadesc) {
	global $wpseo_front, $mbt_taxonomy_query;
	if(mbt_is_taxonomy_query()) {
		if($mbt_taxonomy_query->is_tax('mbt_author')) {
			$metadesc = WPSEO_Taxonomy_Meta::get_term_meta($mbt_taxonomy_query->get('mbt_author'), 'mbt_author', 'desc');
			if(!$metadesc) {
				if(isset($wpseo_front->options['metadesc-mbt_author'])) {
					$metadesc = wpseo_replace_vars($wpseo_front->options['metadesc-mbt_author'], get_term_by('slug', $mbt_taxonomy_query->get('mbt_author'), 'mbt_author', ARRAY_A));
				} else {
					$metadesc = mbt_seo_tax_metadesc($mbt_taxonomy_query->get('mbt_author'), 'mbt_author');
				}
			}
		} else if($mbt_taxonomy_query->is_tax('mbt_series')) {
			$metadesc = WPSEO_Taxonomy_Meta::get_term_meta($mbt_taxonomy_query->get('mbt_series'), 'mbt_series', 'desc');
			if(!$metadesc) {
				if(isset($wpseo_front->options['metadesc-mbt_series'])) {
					$metadesc = wpseo_replace_vars($wpseo_front->options['metadesc-mbt_series'], get_term_by('slug', $mbt_taxonomy_query->get('mbt_series'), 'mbt_series', ARRAY_A));
				} else {
					$metadesc = mbt_seo_tax_metadesc($mbt_taxonomy_query->get('mbt_series'), 'mbt_series');
				}
			}
		} else if($mbt_taxonomy_query->is_tax('mbt_genre')) {
			$metadesc = WPSEO_Taxonomy_Meta::get_term_meta($mbt_taxonomy_query->get('mbt_genre'), 'mbt_genre', 'desc');
			if(!$metadesc) {
				if(isset($wpseo_front->options['metadesc-mbt_genre'])) {
					$metadesc = wpseo_replace_vars($wpseo_front->options['metadesc-mbt_genre'], get_term_by('slug', $mbt_taxonomy_query->get('mbt_genre'), 'mbt_genre', ARRAY_A));
				} else {
					$metadesc = mbt_seo_tax_metadesc($mbt_taxonomy_query->get('mbt_genre'), 'mbt_genre');
				}
			}
		} else if($mbt_taxonomy_query->is_tax('mbt_tag')) {
			$metadesc = WPSEO_Taxonomy_Meta::get_term_meta($mbt_taxonomy_query->get('mbt_tag'), 'mbt_tag', 'desc');
			if(!$metadesc) {
				if(isset($wpseo_front->options['metadesc-mbt_tag'])) {
					$metadesc = wpseo_replace_vars($wpseo_front->options['metadesc-mbt_tag'], get_term_by('slug', $mbt_taxonomy_query->get('mbt_tag'), 'mbt_tag', ARRAY_A));
				} else {
					$metadesc = mbt_seo_tax_metadesc($mbt_taxonomy_query->get('mbt_tag'), 'mbt_tag');
				}
			}
		}
	}
	return $metadesc;
}

function mbt_filter_wpseo_canonical($canonical) {
	global $mbt_taxonomy_query;
	if(mbt_is_taxonomy_query()) {
		if($mbt_taxonomy_query->is_tax('mbt_author')) {
			$canonical = get_term_link($mbt_taxonomy_query->get('mbt_author'), 'mbt_author');
		} else if($mbt_taxonomy_query->is_tax('mbt_series')) {
			$canonical = get_term_link($mbt_taxonomy_query->get('mbt_series'), 'mbt_series');
		} else if($mbt_taxonomy_query->is_tax('mbt_genre')) {
			$canonical = get_term_link($mbt_taxonomy_query->get('mbt_genre'), 'mbt_genre');
		} else if($mbt_taxonomy_query->is_tax('mbt_tag')) {
			$canonical = get_term_link($mbt_taxonomy_query->get('mbt_tag'), 'mbt_tag');
		}
	}
	return $canonical;
}

function mbt_filter_wpseo_title($title) {
	global $wpseo_front, $mbt_taxonomy_query;
	if(mbt_is_taxonomy_query()) {
		if($mbt_taxonomy_query->is_tax('mbt_author')) {
			$title = $wpseo_front->get_title_from_options('title-mbt_author', get_term_by('slug', $mbt_taxonomy_query->get('mbt_author'), 'mbt_author', ARRAY_A));
			if(empty($title)) { $title = mbt_seo_tax_title($mbt_taxonomy_query->get('mbt_author'), 'mbt_author'); }
		} else if($mbt_taxonomy_query->is_tax('mbt_series')) {
			$title = $wpseo_front->get_title_from_options('title-mbt_series', get_term_by('slug', $mbt_taxonomy_query->get('mbt_series'), 'mbt_series', ARRAY_A));
			if(empty($title)) { $title = mbt_seo_tax_title($mbt_taxonomy_query->get('mbt_series'), 'mbt_series'); }
		} else if($mbt_taxonomy_query->is_tax('mbt_genre')) {
			$title = $wpseo_front->get_title_from_options('title-mbt_genre', get_term_by('slug', $mbt_taxonomy_query->get('mbt_genre'), 'mbt_genre', ARRAY_A));
			if(empty($title)) { $title = mbt_seo_tax_title($mbt_taxonomy_query->get('mbt_genre'), 'mbt_genre'); }
		} else if($mbt_taxonomy_query->is_tax('mbt_tag')) {
			$title = $wpseo_front->get_title_from_options('title-mbt_tag', get_term_by('slug', $mbt_taxonomy_query->get('mbt_tag'), 'mbt_tag', ARRAY_A));
			if(empty($title)) { $title = mbt_seo_tax_title($mbt_taxonomy_query->get('mbt_tag'), 'mbt_tag'); }
		}
	}
	return $title;
}

function mbt_filter_wpseo_opengraph_type($type) {
	if(is_singular('mbt_book')) {
		$type = 'book';
	}
	return $type;
}

function mbt_add_wpseo_opengraph_image() {
	global $mbt_taxonomy_query, $post;
	if(is_tax('mbt_author') or is_tax('mbt_series') or is_tax('mbt_genre') or is_tax('mbt_tag')) {
		$query_obj = get_queried_object();
		if(!empty($query_obj) and !empty($query_obj->taxonomy)) {
			$image = mbt_get_taxonomy_image($query_obj->taxonomy, $query_obj->term_id);
		}
	} else if(mbt_is_taxonomy_query()) {
		$query_obj = $mbt_taxonomy_query->get_queried_object();
		if(!empty($query_obj) and !empty($query_obj->taxonomy)) {
			$image = mbt_get_taxonomy_image($query_obj->taxonomy, $query_obj->term_id);
		}
	} else if(is_singular('mbt_book')) {
		$image = mbt_get_book_image_src($post->ID);
		$image = $image[0];
	}

	if(!empty($image)) {
		echo("<meta property='og:image' content='".esc_url($image)."'/>\n");
		return true;
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
			$title = get_the_title().(empty($authors) ? "" : " by ".$authors).((get_option('template') === "twentyten" or get_option('template') === "twentyeleven") ? " " : "");
		} else {
			$title = $seo_title." ";
		}
	}

	return $title;
}

function mbt_seo_tax_title($term = '', $taxonomy = '') {
	$title = '';

	if(empty($term) or empty($taxonomy)) {
		if(mbt_is_taxonomy_query()) {
			global $mbt_taxonomy_query;
			$term_obj = $mbt_taxonomy_query->get_queried_object();
		} else {
			$term_obj = get_queried_object();
		}
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

	return $metadesc;
}

function mbt_seo_tax_metadesc($term = '', $taxonomy = '') {
	$metadesc = '';

	if(empty($term) or empty($taxonomy)) {
		if(mbt_is_taxonomy_query()) {
			global $mbt_taxonomy_query;
			$term_obj = $mbt_taxonomy_query->get_queried_object();
		} else {
			$term_obj = get_queried_object();
		}
	} else {
		$term_obj = get_term_by('slug', $term, $taxonomy);
	}
	if($term_obj and !empty($term_obj->description)) {
		$metadesc = $term_obj->description;
	}

	return $metadesc;
}

function mbt_seo_wp_title($title) {
	$new_title = '';

	if(is_tax('mbt_author') or is_tax('mbt_series') or is_tax('mbt_genre') or is_tax('mbt_tag') or mbt_is_taxonomy_query()) {
		$new_title = mbt_seo_tax_title();
	} else if(is_singular('mbt_book')) {
		$new_title = mbt_seo_title();
	}
	if(!empty($new_title)) {
		if(substr($title, 0, 7) == "<title>") { $new_title = '<title>'.$new_title.'</title>'; }
		$title = $new_title;
	}

	return $title;
}

function mbt_seo_add_metadesc() {
	if(is_tax('mbt_author') or is_tax('mbt_series') or is_tax('mbt_genre') or is_tax('mbt_tag') or mbt_is_taxonomy_query()) {
		$metadesc = mbt_seo_tax_metadesc();
		if($metadesc) {
			echo('<meta name="description" content="'.$metadesc."\"/>\n");
		}
	} else if(is_singular('mbt_book')) {
		$metadesc = mbt_seo_metadesc();
		if($metadesc) {
			echo('<meta name="description" content="'.$metadesc."\"/>\n");
		}
	}
}

function mbt_seo_add_opengraph() {
	global $mbt_taxonomy_query, $post;
	$tags = array();

	if(is_tax('mbt_author') or is_tax('mbt_series') or is_tax('mbt_genre') or is_tax('mbt_tag') or mbt_is_taxonomy_query()) {
		$tags['og:type'] = 'website';
		$tags['og:title'] = mbt_seo_tax_title();
		$tags['og:description'] = mbt_seo_tax_metadesc();
		if(mbt_is_taxonomy_query()) {
			if($mbt_taxonomy_query->is_tax('mbt_author')) {
				$tags['og:url'] = esc_url(get_term_link($mbt_taxonomy_query->get('mbt_author'), 'mbt_author'));
			} else if($mbt_taxonomy_query->is_tax('mbt_series')) {
				$tags['og:url'] = esc_url(get_term_link($mbt_taxonomy_query->get('mbt_series'), 'mbt_series'));
			} else if($mbt_taxonomy_query->is_tax('mbt_genre')) {
				$tags['og:url'] = esc_url(get_term_link($mbt_taxonomy_query->get('mbt_genre'), 'mbt_genre'));
			} else if($mbt_taxonomy_query->is_tax('mbt_tag')) {
				$tags['og:url'] = esc_url(get_term_link($mbt_taxonomy_query->get('mbt_tag'), 'mbt_tag'));
			}

			$query_obj = $mbt_taxonomy_query->get_queried_object();
			if(!empty($query_obj) and !empty($query_obj->taxonomy)) {
				$tags['og:image'] = esc_url(mbt_get_taxonomy_image($query_obj->taxonomy, $query_obj->term_id));
			}
		} else {
			if(is_tax('mbt_author')) {
				$tags['og:url'] = esc_url(get_term_link(get_query_var('mbt_author'), 'mbt_author'));
			} else if(is_tax('mbt_series')) {
				$tags['og:url'] = esc_url(get_term_link(get_query_var('mbt_series'), 'mbt_series'));
			} else if(is_tax('mbt_genre')) {
				$tags['og:url'] = esc_url(get_term_link(get_query_var('mbt_genre'), 'mbt_genre'));
			} else if(is_tax('mbt_tag')) {
				$tags['og:url'] = esc_url(get_term_link(get_query_var('mbt_tag'), 'mbt_tag'));
			}

			$query_obj = get_queried_object();
			if(!empty($query_obj) and !empty($query_obj->taxonomy)) {
				$tags['og:image'] = esc_url(mbt_get_taxonomy_image($query_obj->taxonomy, $query_obj->term_id));
			}
		}
		$tags['og:site_name'] = get_bloginfo('name');
	} else if(is_singular('mbt_book')) {
		$tags['og:type'] = 'book';
		$tags['og:title'] = mbt_seo_title();
		$tags['og:description'] = mbt_seo_metadesc();
		$tags['og:url'] = esc_url(get_permalink());
		$tags['og:site_name'] = get_bloginfo('name');
		$image = mbt_get_book_image_src($post->ID);
		$tags['og:image'] = esc_url($image[0]);
		$isbn = get_post_meta($post->ID, 'mbt_unique_id', true);
		if(!empty($isbn)) { $tags['book:isbn'] = $isbn; }
	}

	foreach($tags as $tag => $content) {
		echo('<meta property="'.$tag.'" content="'.$content.'"/>');
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