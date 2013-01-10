<?php

/*---------------------------------------------------------*/
/* WPSEO Integration Functions                             */
/*---------------------------------------------------------*/

function mbt_filter_wpseo_options($options) {
	if(!isset($options['title-mbt_books']) or empty($options['title-mbt_books'])) {
		$options['title-mbt_books'] = '%%title%% by %%ct_mbt_authors%%';
		$options['metadesc-mbt_books'] = '%%excerpt%%';
	}
	if(!isset($options['title-mbt_authors']) or empty($options['title-mbt_authors'])) {
		$options['title-mbt_authors'] = '%%term_title%% | %%sitename%%';
		$options['metadesc-mbt_authors'] = '%%term_description%%';
	}
	if(!isset($options['title-mbt_series']) or empty($options['title-mbt_series'])) {
		$options['title-mbt_series'] = '%%term_title%% | %%sitename%%';
		$options['metadesc-mbt_series'] = '%%term_description%%';
	}
	if(!isset($options['title-mbt_genres']) or empty($options['title-mbt_genres'])) {
		$options['title-mbt_genres'] = '%%term_title%% | %%sitename%%';
		$options['metadesc-mbt_genres'] = '%%term_description%%';
	}
	
	return $options;
}
add_filter('option_wpseo_titles', 'mbt_filter_wpseo_options');

function mbt_force_filter_wpseo_options($options) {
	$options['title-mbt_books'] = '%%title%% by %%ct_mbt_authors%%';
	$options['metadesc-mbt_books'] = '%%excerpt_only%%';
	$options['title-mbt_authors'] = '%%term_title%% | %%sitename%%';
	$options['metadesc-mbt_authors'] = '%%term_description%%';
	$options['title-mbt_series'] = '%%term_title%% | %%sitename%%';
	$options['metadesc-mbt_series'] = '%%term_description%%';
	$options['title-mbt_genres'] = '%%term_title%% | %%sitename%%';
	$options['metadesc-mbt_genres'] = '%%term_description%%';
	
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
	if(mbt_is_seo_active() and is_singular('mbt_books')) {
		global $post;
		$seo_title = get_post_meta($post->ID, 'mbt_seo_title', true);
		if(empty($seo_title)) {
			$terms = get_the_terms($post->ID, "mbt_authors");
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
	if(mbt_is_seo_active() and is_singular('mbt_books')) {
		global $post;
		$seo_metadesc = get_post_meta($post->ID, 'mbt_seo_metadesc', true);
		if(empty($seo_metadesc)) {
			echo('<meta name="description" content="'.get_the_excerpt().'"/>');
		} else {
			echo('<meta name="description" content="'.$seo_metadesc.'"/>');
		}
	}
}
add_action('wp_head', 'mbt_seo_add_metadesc');
