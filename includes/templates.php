<?php

/*---------------------------------------------------------*/
/* Template Overload Functions                             */
/*---------------------------------------------------------*/

//enqueue frontend plugin styles
add_action('wp_enqueue_scripts', 'mbt_enqueue_styles');
function mbt_enqueue_styles() {
	wp_enqueue_style('book-table-style', plugins_url('css/frontend-style.css', dirname(__FILE__)));
}

function mbt_locate_template($name) {
	$locatedtemplate = locate_template($name);
	return empty($locatedtemplate) ? plugin_dir_path(dirname(__FILE__)).'templates/'.$name : $locatedtemplate;
}

function mbt_load_book_templates($template) {
	if(is_post_type_archive('mbt_books') or is_tax('mbt_authors') or is_tax('mbt_genres') or is_tax('mbt_series')) {
		$template = mbt_locate_template('archive-books.php');
	}

	if(is_singular('mbt_books')) {
		$template = mbt_locate_template('single-books.php');
	}

	return $template;
}
add_filter('template_include', 'mbt_load_book_templates');

//add image sizes
function mbt_add_image_size() {
	if(function_exists('add_image_size')) {
		add_image_size('book-image', 300, 300);
	}
}
add_action('init', 'mbt_add_image_size');


//change the number of posts per page for the book archives
function mbt_set_books_posts_per_page($query) {
	if(get_post_type() != 'mbt_books' and $query->is_main_query()) { // is only for min_book archives and only on main query
		$query->query_vars['posts_per_page'] = !empty($mbt_get_settings['posts_per_page']) ? mbt_get_setting('posts_per_page') : get_option('posts_per_page');
	}
}
add_action('pre_get_posts', 'mbt_set_books_posts_per_page');

//function for booktable page
function mbt_booktable_shortcode( $atts ) {
?>
	<?php
		global $wp_query, $post;
		$old_wp_query = $wp_query;
		$wp_query = new WP_Query(array('post_type' => 'mbt_books'));
	?>
	
	<?php if(have_posts()) { ?>

		<?php while(have_posts()){ the_post(); ?>

				<?php include(mbt_locate_template("content-books.php")); ?>

		<?php } ?>

	<?php } else { ?>

		<article id="post-0" class="post no-results not-found">
			<header class="entry-header">
				<h1 class="entry-title"><?php _e('Nothing Found', 'twentyeleven'); ?></h1>
			</header><!-- .entry-header -->

			<div class="entry-content">
				<p><?php _e('Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'twentyeleven'); ?></p>
				<?php get_search_form(); ?>
			</div><!-- .entry-content -->
		</article><!-- #post-0 -->

	<?php } ?>

<?php
	$wp_query = $old_wp_query;
}
add_shortcode('mbt_booktable', 'mbt_booktable_shortcode');

//override body class
add_filter('body_class', 'mbt_body_class', 100);
function mbt_body_class($classes) {
	if(is_singular('mbt_books')) {
		$key = array_search('singular', $classes);
		if($key !== false ) { unset($classes[$key]); }
		$classes[] = "mbt_page";
	}

	return $classes;
}



/*---------------------------------------------------------*/
/* Content Output Functions                                */
/*---------------------------------------------------------*/

//format image
function mbt_format_image($post_id) {
	$src = '';

	$image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'book-image');
	if($image) {
		list($src, $width, $height) = $image;
	} else {
		$src = plugins_url('images/book-placeholder.png', dirname(__FILE__));
	}
	return '<img itemprop="image" src="'.$src.'" class="mbt-book-image">';
}

//format price
function mbt_format_price($post_id) {
	$price = get_post_meta($post_id, 'mbt_price', true);
	$sale_price = get_post_meta($post_id, 'mbt_sale_price', true);

	if(!empty($price) and !empty($sale_price)) {
		return '<div itemprop="offers" itemscope="" itemtype="http://schema.org/Offer" class="mbt-book-price">
					<span itemprop="price" class="price">
						<span class="oldprice">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $price), 2).'</span>
						<span class="newprice">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $sale_price), 2).'</span>
					</span>
				</div>';
	} elseif(!empty($price)) {
		return '<div itemprop="offers" itemscope="" itemtype="http://schema.org/Offer" class="mbt-book-price">
					<span itemprop="price" class="price">'.'$'.number_format(preg_replace("/[^0-9,.]/", "", $price), 2).'</span>
				</div>';
	}
	return '';
}

//format the buttons for the book
function mbt_get_book_buttons($post_id, $featured_only = false) {
	$output = '<div class="mbt-book-buttons">';

	if($featured_only) { $featured_buybuttons = mbt_get_setting('featured_buybuttons'); }
	$buybuttons = mbt_get_buybuttons();
	$post_buybuttons = get_post_meta($post_id, "mbt_buybuttons", true);
	if(!empty($post_buybuttons)) {
		for($i = 0; $i < count($post_buybuttons); $i++)
		{
			if($featured_only) { if(!isset($featured_buybuttons[$post_buybuttons[$i]['type']])) { continue; } }
			$output .= $buybuttons[$post_buybuttons[$i]['type']]['button']($post_buybuttons[$i]);
		}
	}

	$output .= '</div>';

	return $output;
}

function mbt_get_book_series($post_id) {
	$output = '';
	$series_all = wp_get_post_terms($post_id, 'mbt_series');
	if(!empty($series_all)) {
		foreach($series_all as $series) {
			$relatedbooks = new WP_Query(array('mbt_series' => $series->slug, 'post__not_in' => array($post_id)));
			if(!empty($relatedbooks->posts)) {
				$output .= '<div class="mbt-book-series">';
				$output .= '<div class="mbt-book-series-title">Other books in "'.$series->name.'":</div>';
				foreach($relatedbooks->posts as $relatedbook) {
					$output .= '<div class="mbt-book">';
					$output .= '<div class="mbt-book-title"><a href="'.get_permalink($relatedbook->ID).'">'.$relatedbook->post_title.'</a></div>';
					$output .= '<div class="mbt-book-images"><a href="'.get_permalink($relatedbook->ID).'">'.mbt_format_image($relatedbook->ID).'</a></div>';
					$output .= '<div class="clear:both"></div>';
					$output .= '</div>';
				}
				$output .= '</div>';
			}
		}
	}
	return $output;
}
