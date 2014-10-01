<?php

function mbt_register_widgets() {
	register_widget("MBT_Featured_Book");
	register_widget("MBT_Taxonomies");
}
add_action('widgets_init', 'mbt_register_widgets');

/*---------------------------------------------------------*/
/* Featured Books Widget                                   */
/*---------------------------------------------------------*/

class MBT_Featured_Book extends WP_Widget {
	function MBT_Featured_Book() {
		$widget_ops = array('classname' => 'mbt_featured_book', 'description' => __("Displays featured or random books.", 'mybooktable'));
		parent::WP_Widget('mbt_featured_book', __('MyBookTable Featured Books', 'mybooktable'), $widget_ops);
		add_action('admin_enqueue_scripts', array('MBT_Featured_Book', 'enqueue_widget_js'));
		$this->defaultargs = array('title' => __('Featured Books', 'mybooktable'), 'selectmode' => 'by_date', 'featured_books' => array(), 'image_size' => 'medium', 'num_books' => 1, 'show_blurb' => true);
	}

	public static function enqueue_widget_js() {
		global $pagenow;
		if($pagenow == 'widgets.php') {
			wp_enqueue_script("mbt-widgets", plugins_url('js/widgets.js', dirname(dirname(__FILE__))), 'jquery', '', true);
		}
	}

	function widget($args, $instance) {
		extract(wp_parse_args($instance, $this->defaultargs));

		$num_books = intval($num_books);
		if($num_books > 10 or $num_books < 1) { $num_books = 1; }
		if(!empty($featured_book)) { $featured_books = array((int)$featured_book); }

		echo($args['before_widget']);
		if($title) { echo($args['before_title'].$title.$args['after_title']); }

		if($selectmode == 'manual_select' and !empty($featured_books)) {
			$books = array();
			foreach($featured_books as $featured_book) {
				$new_book = get_post($featured_book);
				if($new_book) { $books[] = $new_book; }
			}
		} else if($selectmode == 'random') {
			$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'posts_per_page' => -1));
			$books = array();
			$keys = array_rand($wp_query->posts, $num_books);
			if(!is_array($keys)) { $keys = array($keys); }
			foreach($keys as $key) {
				$books[] = $wp_query->posts[$key];
			}
		} else {
			$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'orderby' => 'date', 'posts_per_page' => $num_books));
			$books = $wp_query->posts;
		}

		if(!empty($books)) {
			?> <div class="mbt-featured-book-widget"> <?php
			foreach($books as $book) {
				$permalink = get_permalink($book->ID);
				?>
					<div class="mbt-featured-book-widget-book">
						<h1 class="mbt-book-title widget-title"><a href="<?php echo($permalink); ?>"><?php echo(get_the_title($book->ID)); ?></a></h1>
						<div class="mbt-book-images"><a href="<?php echo($permalink); ?>"><?php echo(mbt_get_book_image($book->ID, array('class' => $image_size))); ?></a></div>
						<?php if($show_blurb) { ?><div class="mbt-book-blurb"><?php echo(mbt_get_book_blurb($book->ID, true)); ?></div><?php } ?>
						<div class="mbt-book-buybuttons">
							<?php
								$buybuttons = mbt_get_buybuttons($book->ID, array('display' => 'featured'));
								echo(mbt_format_buybuttons($buybuttons));
							?>
							<div style="clear:both;"></div>
						</div>
					</div>
				<?php
			}
			?> </div> <?php
		}

		echo('<div style="clear:both;"></div>');
		echo($args['after_widget']);
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['selectmode'] = strip_tags($new_instance['selectmode']);
		$instance['image_size'] = $new_instance['image_size'];
		$instance['num_books'] = intval($new_instance['num_books']);
		$instance['show_blurb'] = (bool)$new_instance['show_blurb'];
		$instance['featured_books'] = (array)json_decode($new_instance['featured_books']);
		unset($instance['featured_book']);
		return $instance;
	}

	function form($instance) {
		extract(wp_parse_args($instance, $this->defaultargs));
		if(!empty($featured_book)) { $featured_books = array((int)$featured_book); }
		?>

		<div class="mbt-featured-book-widget-editor" onmouseover="mbt_initialize_featured_book_widget_editor(this);">
			<p>
				<label><?php _e('Title', 'mybooktable'); ?>: <input type="text" name="<?php echo($this->get_field_name('title')); ?>" value="<?php echo($title); ?>"></label>
			</p>
			<p>
				<label><?php _e('Book image size:', 'mybooktable'); ?><br>
				<?php $sizes = array('small' =>__('Small', 'mybooktable'), 'medium' => __('Medium', 'mybooktable'), 'large' => __('Large', 'mybooktable')); ?>
				<?php foreach($sizes as $size => $size_name) { ?>
					<input type="radio" name="<?php echo($this->get_field_name('image_size')); ?>" value="<?php echo($size); ?>" <?php echo($image_size == $size ? ' checked' : ''); ?> ><?php echo($size_name); ?><br>
				<?php } ?>
				</label>
			</p>
			<p>
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_blurb'); ?>" name="<?php echo $this->get_field_name('show_blurb'); ?>"<?php checked($show_blurb); ?> />
				<label for="<?php echo $this->get_field_id('show_blurb'); ?>"><?php _e('Show book blurb', 'mybooktable'); ?></label>
			</p>
			<p>
				<label for="<?php echo($this->get_field_id('selectmode')); ?>"><?php _e('Choose how to select the featured books:', 'mybooktable'); ?></label>
				<select class="mbt_featured_book_selectmode" name="<?php echo($this->get_field_name('selectmode')); ?>" id="<?php echo($this->get_field_id('selectmode')); ?>">
					<option value="by_date"<?php selected($selectmode, 'by_date'); ?>><?php _e('Most Recent Books', 'mybooktable'); ?></option>
					<option value="manual_select"<?php selected($selectmode, 'manual_select'); ?>><?php _e('Choose Manually', 'mybooktable'); ?></option>
					<option value="random"<?php selected($selectmode, 'random'); ?>><?php _e('Random Books', 'mybooktable'); ?></option>
				</select>
			</p>
			<div class="mbt-featured-book-manual-selector" <?php echo($selectmode === 'manual_select' ? '' : 'style="display:none"'); ?>>
				<label for="mbt-book-selector"><?php _e('Select Books:', 'mybooktable'); ?></label></br>
				<select class="mbt-featured-book-selector">
					<option value=""><?php _e('-- Choose One --', 'mybooktable'); ?></option>
					<?php
						$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'orderby' => 'title', 'order' => 'ASC', 'posts_per_page' => -1));
						if(!empty($wp_query->posts)) {
							foreach($wp_query->posts as $book) {
								echo '<option value="'.$book->ID.'">'.substr($book->post_title, 0, 25).(strlen($book->post_title) > 25 ? '...' : '').'</option>';
							}
						}
					?>
				</select>
				<input type="button" class="mbt-featured-book-adder button" value="<?php _e('Add', 'mybooktable'); ?>" /><br>

				<?php
					echo('<ul class="mbt-featured-book-list">');
					foreach($featured_books as $featured_book) {
						$book = get_post($featured_book);
						if($book) {
							echo('<li data-id="'.$book->ID.'" class="mbt-book">'.substr($book->post_title, 0, 25).(strlen($book->post_title) > 25 ? '...' : '').'<a class="mbt-book-remover">X</a></li>');
						}
					}
					echo('</ul>');
				?>
				<input class="mbt-featured-books" id="<?php echo($this->get_field_id('featured_books')); ?>" name="<?php echo($this->get_field_name('featured_books')); ?>" type="hidden" value="<?php echo(json_encode($featured_books)); ?>">
			</div>
			<div class="mbt-featured-book-options" <?php echo($selectmode !== 'manual_select' ? '' : 'style="display:none"'); ?>>
				<p>
					<label><?php _e('Number of Books:', 'mybooktable'); ?>
						<input type="number" name="<?php echo($this->get_field_name('num_books')); ?>" value="<?php echo(intval($num_books ? $num_books : 1)); ?>"  min="1" max="10">
					</label>
				</p>
			</div>
		</div>

		<?php
	}
}

/*---------------------------------------------------------*/
/* Taxonomy Widget                                         */
/*---------------------------------------------------------*/

class MBT_Taxonomies extends WP_Widget {
	function MBT_Taxonomies() {
		$widget_ops = array('classname' => 'mbt_taxonomies', 'description' => __("A list of Authors, Genres, Series, or Tags.", 'mybooktable'));
		parent::WP_Widget('mbt_taxonomies', __('MyBookTable Taxonomy Widget', 'mybooktable'), $widget_ops);
	}

	function widget($args, $instance) {
		extract($args);

		$tax = empty($instance['tax']) ? 'mbt_author' : $instance['tax'];
		if($tax === 'mbt_genre') {
			$title = __('Genres', 'mybooktable');
		} else if($tax === 'mbt_series') {
			$title = __('Series', 'mybooktable');
		} else if($tax === 'mbt_tag') {
			$title = __('Tags', 'mybooktable');
		} else {
			$tax = 'mbt_author';
			$title = __('Authors', 'mybooktable');
		}
		if(!empty($instance['title'])) { $title = $instance['title']; }
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		$c = !empty($instance['count']) ? '1' : '0';

		echo($before_widget);
		if($title) { echo($before_title.$title.$after_title); }

		$args = array('orderby' => 'name', 'title_li' => '', 'show_count' => $c, 'taxonomy' => $tax);

		echo('<ul>');
		wp_list_categories(apply_filters('mbt_taxonomy_widget_args', $args));
		echo('</ul>');

		echo($after_widget);
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['tax'] = $new_instance['tax'];
		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args((array)$instance, array('title' => ''));
		$count = isset($instance['count']) ? (bool)$instance['count'] : false;
		$tax = isset($instance['tax']) ? $instance['tax'] : '';

		?>
			<label><?php _e('Title', 'mybooktable'); ?>: <input type="text" name="<?php echo($this->get_field_name('title')); ?>" value="<?php echo($instance['title']); ?>"></label>
			<br /><br />
			<label for="<?php echo $this->get_field_id('tax'); ?>"><?php _e('Displayed taxonomy', 'mybooktable'); ?>:</label>
			<select name="<?php echo $this->get_field_name('tax'); ?>" id="<?php echo $this->get_field_id('tax'); ?>" class="widefat">
				<option value=""><?php _e('-- Choose One --', 'mybooktable'); ?></option>
				<option value="mbt_author"<?php selected($tax, 'mbt_author'); ?>><?php _e('Authors', 'mybooktable'); ?></option>
				<option value="mbt_genre"<?php selected($tax, 'mbt_genre'); ?>><?php _e('Genres', 'mybooktable'); ?></option>
				<option value="mbt_series"<?php selected($tax, 'mbt_series'); ?>><?php _e('Series', 'mybooktable'); ?></option>
				<option value="mbt_tag"<?php selected($tax, 'mbt_tag'); ?>><?php _e('Tags', 'mybooktable'); ?></option>
			</select>
			<br /><br />

			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked($count); ?> />
			<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Show post counts', 'mybooktable'); ?></label><br />
		<?php
	}
}
