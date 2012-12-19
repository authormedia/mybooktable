<?php

//register custom plugin widgets
add_action('widgets_init', 'mbt_register_widgets');
function mbt_register_widgets() {
	register_widget("MBT_Featured_Books");
	register_widget("MBT_Latest_Book");
	register_widget("MBT_Book_Categories");
}



/*------------------------------------------*/
/* Featured Books Widget                    */
/*------------------------------------------*/
class MBT_Featured_Books extends WP_Widget {
	function MBT_Featured_Books() {
		parent::WP_Widget('mbt_featured_books', 'Featured Books');	
	}

	function widget($args, $instance) {
		extract(wp_parse_args($instance, array('numprod' => 1, 'hasimage' => 'on', 'hastitle' => 'on', 'hasexcerpt' => 'on', 'excerptlen' => 100, 'width' => 200, 'height' => 300)));
		
		//filter in case someone put in units. Ex: "300px"
		$width = preg_replace("/[^0-9]/","", $width);  
		$height = preg_replace("/[^0-9]/","", $height);


		echo($args['before_widget']);
		if(!empty($title)) { echo($args['before_title'].$title.$args['after_title']); }
		if(!empty($message)) { echo('<div class="message">'.$message.'</div>'); }

		$featuredbooks = new WP_Query(array('posts_per_page' => $numprod, 'orderby' => 'menu_order', 'taxonomy' => 'book_category', 'meta_key' => 'mbt_featured', 'meta_value' => 'on', 'order' => 'DESC', 'post_type' => 'mbt_books', 'post_status' => 'publish'));
		
		if(!empty($featuredbooks->posts)) {
			foreach($featuredbooks->posts as $book) {
				echo('<div class="featurebook">');

				if($hasimage == 'on') { echo '<div class="image"><a href="'.get_permalink($book->ID).'">'.mbt_get_book_image($book, $width, $height).'</a></div>'; }
				if($hastitle == 'on') { echo '<div class="image"><a href="'.get_permalink($book->ID).'">'.$book->post_title.'</a></div>'; }
				if($hasexcerpt == 'on') {
					echo('<div class="excerpt">');
					echo(string_limit_chars(!empty($book->post_excerpt) ? $book->post_excerpt : strip_tags($book->post_content), $excerptlen));
					echo('<span class="readmore"><a href="'.get_permalink($book->ID).'">Read More &raquo;</a></span>');
					echo('</div>');
				} else {
					echo('<div class="readmore"><a href="'.get_permalink($book->ID).'">Read More &raquo;</a></div>');
				}
				
				echo('</div> <!-- end featurebook -->');
			}
		}

		echo('<div style="clear:both;"></div>');
		echo($args['after_widget']);
	}
 
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['message'] = strip_tags($new_instance['message']);
		$instance['width'] = strip_tags($new_instance['width']);
		$instance['height'] = strip_tags($new_instance['height']);
		$instance['numprod'] = strip_tags($new_instance['numprod']);
		$instance['hasimage'] = strip_tags($new_instance['hasimage']);
		$instance['hastitle'] = strip_tags($new_instance['hastitle']);
		$instance['hasexcerpt'] = strip_tags($new_instance['hasexcerpt']);
		$instance['excerptlen'] = strip_tags($new_instance['excerptlen']);

		return $instance;
	}

	function form($instance) {
		if(empty($instance)) {
			$instance = array('title' => '', 'message' => '', 'numprod' => '', 'hasimage' => '', 'hastitle' => '', 'hasexcerpt' => '', 'excerptlen' => '', 'width' => '', 'height' => '');
		}
		$title 		= esc_attr($instance['title']);
		$message	= esc_attr($instance['message']);
		$numprod	= esc_attr($instance['numprod']);
		$hasimage	= esc_attr($instance['hasimage']);
		$hastitle	= esc_attr($instance['hastitle']);
		$hasexcerpt	= esc_attr($instance['hasexcerpt']);
		$excerptlen	= esc_attr($instance['excerptlen']);
		$width		= esc_attr($instance['width']);
		$height		= esc_attr($instance['height']);
		?>

		<p>Displays books that are selected as featured</p>
		 <p>
			<label for="<?php echo($this->get_field_id('title')); ?>">Title:</label>
			<input class="widefat" id="<?php echo($this->get_field_id('title')); ?>" name="<?php echo($this->get_field_name('title')); ?>" type="text" value="<?php echo($title); ?>" />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('message')); ?>">Intro: (optional):</label>
			<input class="widefat" id="<?php echo($this->get_field_id('message')); ?>" name="<?php echo($this->get_field_name('message')); ?>" type="text" value="<?php echo($message); ?>" />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('numprod')); ?>">Number of Books to Display:</label>
			<select name="<?php echo($this->get_field_name('numprod')); ?>" id="<?php echo($this->get_field_id('numprod')); ?>">
				<option value="1"<?php selected($numprod, 1); ?>>1</option>
				<option value="2"<?php selected($numprod, 2); ?>>2</option>
				<option value="3"<?php selected($numprod, 3); ?>>3</option>
				<option value="4"<?php selected($numprod, 4); ?>>4</option>
				<option value="5"<?php selected($numprod, 5); ?>>5</option>
				<option value="6"<?php selected($numprod, 6); ?>>6</option>
				<option value="7"<?php selected($numprod, 7); ?>>7</option>
				<option value="8"<?php selected($numprod, 8); ?>>8</option>
				<option value="9"<?php selected($numprod, 9); ?>>9</option>
				<option value="10"<?php selected($numprod, 10); ?>>10</option>
			</select>
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('hasimage')); ?>">Show Book Image:</label>
			<input  id="<?php echo($this->get_field_id('hasimage')); ?>" name="<?php echo($this->get_field_name('hasimage')); ?>" type="checkbox" <?php checked($hasimage, 'on'); ?> />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('width')); ?>">Image Width:</label>
			<input class="" id="<?php echo($this->get_field_id('width')); ?>" name="<?php echo($this->get_field_name('width')); ?>" type="text" size="5" value="<?php echo(empty($width)? 200 : $width); ?>" />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('height')); ?>">Image Height:</label>
			<input class="" id="<?php echo($this->get_field_id('height')); ?>" name="<?php echo($this->get_field_name('height')); ?>" type="text" size="5" value="<?php echo(empty($height)? 300 : $height); ?>" />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('hastitle')); ?>">Show Book Title:</label> 
			<input class="" id="<?php echo($this->get_field_id('hastitle')); ?>" name="<?php echo($this->get_field_name('hastitle')); ?>" type="checkbox" <?php checked($hastitle, 'on'); ?> />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('hasexcerpt')); ?>">Show Excerpt of Book Description:</label> 
			<input class="" id="<?php echo($this->get_field_id('hasexcerpt')); ?>" name="<?php echo($this->get_field_name('hasexcerpt')); ?>" type="checkbox" <?php checked($hasexcerpt, 'on'); ?> />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('excerptlen')); ?>">Length of Book Description (in characters):</label> 
			<input class="" id="<?php echo($this->get_field_id('excerptlen')); ?>" name="<?php echo($this->get_field_name('excerptlen')); ?>" type="text" size="5" value="<?php echo(empty($excerptlen) ? 100 : $excerptlen); ?>" />
		</p>

		<?php 
	}
}





/*------------------------------------------*/
/* Latest Books Widget                      */
/*------------------------------------------*/
class MBT_Latest_Book extends WP_Widget {
	function MBT_Latest_Book() {
		parent::WP_Widget('mbt_latest_book', 'Latest Book');	
	}

	function widget($args, $instance) {
		global $wpdb;
		extract(wp_parse_args($instance, array('selectmode' => 'by_date', 'hasimage' => 'on', 'hastitle' => 'on', 'hasexcerpt' => 'on', 'excerptlen' => 100, 'width' => 200, 'height' => 300)));

		//filter in case someone put in units. Ex: "300px"
		$width = preg_replace("/[^0-9]/","", $width);  
		$height = preg_replace("/[^0-9]/","", $height);


		echo($args['before_widget']);
		if(!empty($title)) { echo($args['before_title'].$title.$args['after_title']); }
		if(!empty($message)) { echo('<div class="message">'.$message.'</div>'); }

		$book = 0;
		if($selectmode != 'manual_select') {
			$book = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'mbt_books' WHERE ID = ".$instance['theprod']." LIMIT 1"));
		} else {
			$book = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'mbt_books' ORDER BY post_date DESC LIMIT 1"));
		}

		if(!empty($book)) {
			echo('<div class="latestbook">');

			if($hasimage == 'on') { echo '<div class="image"><a href="'.get_permalink($book->ID).'">'.mbt_get_book_image($book, $width, $height).'</a></div>'; }
			if($hastitle == 'on') { echo '<div class="image"><a href="'.get_permalink($book->ID).'">'.$book->post_title.'</a></div>'; }
			if($hasexcerpt == 'on') {
				echo('<div class="excerpt">');
				echo(string_limit_chars(!empty($book->post_excerpt) ? $book->post_excerpt : strip_tags($book->post_content), $excerptlen));
				echo('<span class="readmore"><a href="'.get_permalink($book->ID).'">Read More &raquo;</a></span>');
				echo('</div>');
			} else {
				echo('<div class="readmore"><a href="'.get_permalink($book->ID).'">Read More &raquo;</a></div>');
			}
			
			echo('</div> <!-- end latestbook -->');
		}

		echo('<div style="clear:both;"></div>');
		echo($args['after_widget']);
	}
 
	function update($new_instance, $old_instance) {		
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['message'] = strip_tags($new_instance['message']);
		$instance['selectmode'] = strip_tags($new_instance['selectmode']);
		$instance['theprod'] = strip_tags($new_instance['theprod']);
		$instance['hasimage'] = strip_tags($new_instance['hasimage']);
		$instance['hastitle'] = strip_tags($new_instance['hastitle']);
		$instance['hasexcerpt'] = strip_tags($new_instance['hasexcerpt']);
		$instance['excerptlen'] = strip_tags($new_instance['excerptlen']);
		$instance['width'] = strip_tags($new_instance['width']);
		$instance['height'] = strip_tags($new_instance['height']);

		return $instance;
	}
	
	function form($instance) {
		global $wpdb;
		if(empty($instance)) {
			$instance = array('title' => '', 'message' => '', 'selectmode' => '', 'theprod' => '', 'hasimage' => '', 'hastitle' => '', 'hasexcerpt' => '', 'excerptlen' => '', 'width' => '', 'height' => '');
		}
		$title 		= esc_attr($instance['title']);
		$message	= esc_attr($instance['message']);
		$selectmode	= esc_attr($instance['selectmode']);
		$theprod	= esc_attr($instance['theprod']);
		$hasimage	= esc_attr($instance['hasimage']);
		$hastitle	= esc_attr($instance['hastitle']);
		$hasexcerpt	= esc_attr($instance['hasexcerpt']);
		$excerptlen	= esc_attr($instance['excerptlen']);
		$width		= esc_attr($instance['width']);
		$height		= esc_attr($instance['height']);
		?>

		<p>Displays the latest book</p>
		<p>
			<label for="<?php echo($this->get_field_id('title')); ?>">Title:</label>
			<input class="widefat" id="<?php echo($this->get_field_id('title')); ?>" name="<?php echo($this->get_field_name('title')); ?>" type="text" value="<?php echo($title); ?>" />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('message')); ?>">Intro: (optional)</label>
			<input class="widefat" id="<?php echo($this->get_field_id('message')); ?>" name="<?php echo($this->get_field_name('message')); ?>" type="text" value="<?php echo($message); ?>" />
		</p>
		<p>
		  	<label for="<?php echo($this->get_field_id('selectmode')); ?>">Choose how to select the latest book:</label>
			<select name="<?php echo($this->get_field_name('selectmode')); ?>" id="<?php echo($this->get_field_id('selectmode')); ?>">
				<option value="">Choose Option</option>			
				<option value="manual_select"<?php selected( $selectmode, 'manual_select' ); ?>>Choose Manually</option>
				<option value="by_date"<?php selected( $selectmode, 'by_date' ); ?>>Latest by Post Date</option>
			</select>
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('theprod')); ?>">Select Latest Book (for use if using manual select, above):</label>
			<select name="<?php echo($this->get_field_name('theprod')); ?>" id="<?php echo($this->get_field_id('theprod')); ?>">
				<option value=""> -- Choose One -- </option>
				<?php
					$prods = $wpdb->get_results("SELECT ID, post_title FROM wp_posts WHERE post_type = 'mbt_books' AND post_status = 'publish' ORDER BY post_title ASC");
					if(!empty($prods)) {
						foreach($prods as $prod) {
							echo '<option value="'.$prod->ID.'" '.selected($theprod, $prod->ID ).' >'.string_limit_chars($prod->post_title, 20).'</option>';	
						}
					}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('hasimage')); ?>">Show Book Image:</label> 
			<input  id="<?php echo($this->get_field_id('hasimage')); ?>" name="<?php echo($this->get_field_name('hasimage')); ?>" type="checkbox" <?php checked( $hasimage, 'on' ); ?> />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('width')); ?>">Width:</label>
			<input class="" id="<?php echo($this->get_field_id('width')); ?>" name="<?php echo($this->get_field_name('width')); ?>" type="text" size="5" value="<?php echo(empty($width)? 200 : $width); ?>" />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('height')); ?>">Height:</label>
			<input class="" id="<?php echo($this->get_field_id('height')); ?>" name="<?php echo($this->get_field_name('height')); ?>" type="text" size="5" value="<?php echo(empty($height)? 300 : $height); ?>" />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('hastitle')); ?>">Show Book Title:</label>
			<input class="" id="<?php echo($this->get_field_id('hastitle')); ?>" name="<?php echo($this->get_field_name('hastitle')); ?>" type="checkbox" <?php checked( $hastitle, 'on' ); ?> />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('hasexcerpt')); ?>">Show Excerpt of Book Description:</label> 
			<input class="" id="<?php echo($this->get_field_id('hasexcerpt')); ?>" name="<?php echo($this->get_field_name('hasexcerpt')); ?>" type="checkbox" <?php checked( $hasexcerpt, 'on' ); ?> />
		</p>
		<p>
			<label for="<?php echo($this->get_field_id('excerptlen')); ?>">Length of Book Description (in characters):</label> 
			<input class="" id="<?php echo($this->get_field_id('excerptlen')); ?>" name="<?php echo($this->get_field_name('excerptlen')); ?>" type="text" size="5" value="<?php echo(empty($excerptlen)? 100 : $excerptlen); ?>" />
		</p>

		<?php 
	}
}





/*------------------------------------------*/
/* Book Categories Widget                   */
/*------------------------------------------*/
class MBT_Book_Categories extends WP_Widget {
	function MBT_Book_Categories() {
		parent::WP_Widget('mbt_book_categories', 'Book Categories');	
	}

	function widget($args, $instance) {
		extract($args);

		$title = empty($instance['title']) ? 'Book Categories' : $instance['title'];
		$c = !empty($instance['count']) ? '1' : '0';
		$h = !empty($instance['hierarchical']) ? '1' : '0';
		$e = empty($instance['exclude']) ? '' : $instance['exclude'];

		echo($before_widget);
		echo($before_title . $title . $after_title);

		echo('<ul>');
		wp_list_categories(array('taxonomy' => 'mbt_series', 'orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h, 'title_li' => '', 'exclude' => $e));
		echo('</ul>');

		echo($after_widget);
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['exclude'] = strip_tags($new_instance['exclude']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;

		return $instance;
	}

	function form($instance) {
		//Defaults
		$instance = wp_parse_args((array)$instance, array('title' => '', 'exclude' => ''));
		$title = esc_attr($instance['title']);
		$exclude = esc_attr($instance['exclude']);
		$count = isset($instance['count']) ? (bool) $instance['count'] : false;
		$hierarchical = isset($instance['hierarchical']) ? (bool)$instance['hierarchical'] : false;
		
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>">Show post counts</label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>">Show hierarchy</label><br /><br />

		<label for="<?php echo $this->get_field_id('exclude'); ?>">Exclude Categories:<br />(comma-separated list of categories by unique ID)</label>
		<input id="<?php echo $this->get_field_id('exclude'); ?>" name="<?php echo $this->get_field_name('exclude'); ?>" type="text" value="<?php echo $exclude; ?>" /></p></p>
		<?php
	}
}