<?php
/*-----------------------------------------------------------------------------------*/
/* Widgets and Widget Control                                                        */
/*-----------------------------------------------------------------------------------*/

/*------------------------------------------*/
/*   Did you Miss Widget
    aka - featured item                     */
/*------------------------------------------*/
/**
* 
*/
// 
class featured_books extends WP_Widget {
    /** constructor -- name this the same as the class above */
    function featured_books() {
        parent::WP_Widget(false, $name = 'Featured Books');	
    }
    /* @see WP_Widget::widget -- do not rename this */
    function widget($args, $instance) {	
        extract( $args );
        $title 		= apply_filters('widget_title', $instance['title']);
        $message 	= apply_filters('widget_message', $instance['message']);
        $width	 	= apply_filters('widget_width', $instance['width']);
        $height	 	= apply_filters('widget_height', $instance['height']);
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; ?>
							<ul>
								<?php if(!empty($message)){echo '<li><div class="message">'.$message.'</div></li>';} ?>
								<li><?php $this->get_featured_books($instance); ?></li>
							</ul>
							<div style="clear:both;"></div>
              <?php echo $after_widget; ?>
        <?php
    }
 
    /** @see WP_Widget::update -- do not rename this */
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
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {	
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
		<p>
		Displays books that are selected as featured
		</p>
		
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>:</label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('message'); ?>"><?php _e('Intro: (leave blank to omit)'); ?>:</label>
          <input class="widefat" id="<?php echo $this->get_field_id('message'); ?>" name="<?php echo $this->get_field_name('message'); ?>" type="text" value="<?php echo $message; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('numprod'); ?>"><?php _e('Number of Books to Display: default 1'); ?>:</label>
		  
		  <select name="<?php echo $this->get_field_name('numprod'); ?>" id="<?php echo $this->get_field_id('numprod'); ?>">
			<option value="1"<?php selected( $numprod, 1 ); ?>>1</option>
			<option value="2"<?php selected( $numprod, 2 ); ?>>2</option>
			<option value="3"<?php selected( $numprod, 3 ); ?>>3</option>
			<option value="4"<?php selected( $numprod, 4 ); ?>>4</option>
			<option value="5"<?php selected( $numprod, 5 ); ?>>5</option>
			<option value="56"<?php selected( $numprod, 6 ); ?>>6</option>
			<option value="7"<?php selected( $numprod, 7 ); ?>>7</option>
			<option value="8"<?php selected( $numprod, 8 ); ?>>8</option>
			<option value="9"<?php selected( $numprod, 9 ); ?>>9</option>
			<option value="10"<?php selected( $numprod, 10 ); ?>>10</option>
		</select>
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('hasimage'); ?>"><?php _e('Show Book Image'); ?>:</label> 
          <input  id="<?php echo $this->get_field_id('hasimage'); ?>" name="<?php echo $this->get_field_name('hasimage'); ?>" type="checkbox" <?php checked( $hasimage, 'on' ); ?> />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Image Width: (default - 200px)'); ?>:</label>
          <input class="" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" size="5" value="<?php echo $width; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Image Height: (default - 300px)'); ?>:</label>
          <input class="" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" size="5" value="<?php echo $height; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('hastitle'); ?>"><?php _e('Show Book Title'); ?>:</label> 
          <input class="" id="<?php echo $this->get_field_id('hastitle'); ?>" name="<?php echo $this->get_field_name('hastitle'); ?>" type="checkbox" <?php checked( $hastitle, 'on' ); ?> />
        </p>
		
		<p>
          <label for="<?php echo $this->get_field_id('hasexcerpt'); ?>"><?php _e('Show Excerpt of Book Description'); ?>:</label> 
          <input class="" id="<?php echo $this->get_field_id('hasexcerpt'); ?>" name="<?php echo $this->get_field_name('hasexcerpt'); ?>" type="checkbox" <?php checked( $hasexcerpt, 'on' ); ?> />
        </p>
		
		<p>
          <label for="<?php echo $this->get_field_id('excerptlen'); ?>"><?php _e('Length of Book Description (in characters)'); ?>:</label> 
          <input class="" id="<?php echo $this->get_field_id('excerptlen'); ?>" name="<?php echo $this->get_field_name('excerptlen'); ?>" type="text" size="5" value="<?php echo $excerptlen; ?>" />
        </p>

        <?php 
    }
		
	function get_featured_books($instance) {
		/*
		todos
		- setting in books to set as featured
		*/
		global $post;
		    $numprod 	= $instance['numprod']; // number of books to show
			if(!isset($numprod)){$numprod=1;}
			
			$hasimage	= $instance['hasimage']; // show the image or no
			if (!isset($hasimage)){$hasimage='on';}
			
			$hastitle	= $instance['hastitle']; // show book title or no
			if (!isset($hastitle)){$hastitle='on';}
			
			$hasexcerpt	= $instance['hasexcerpt'];
			if (!isset($hasexcerpt)){$hasexcerpt='on';}
			
			$excerptlen	= $instance['excerptlen']; // number of chars to show in excerpt
			if (!isset($excerptlen)){$excerptlen=100;}

			//print_r($instance);
			
			//$num         =  $this->get_field_name('numprod');
			$type         = 'min_products';
			$taxonomy     = 'product_category';
			$orderby      = 'menu_order'; 
			$order        = 'DESC'; 

			$width = $instance['width'];
			$height = $instance['height'];
			
			$width = preg_replace("/[^0-9]/","", $width);  // just in case someone put in alpha chars eg - "300px"
			$height = preg_replace("/[^0-9]/","", $height);  // just in case someone put in alpha chars eg - "300px"

			$args = array(
				'posts_per_page'	=> $numprod,
			//	'nopaging'			=> true,
				'orderby'			=> $orderby,
				'taxonomy'			=> $taxonomy,
				'meta_key'			=> '_cmb_featured',
				'meta_value'		=> 'on',
				'order'				=> $order,
				'post_type'			=> $type,
				'post_status'		=> 'publish', 
			);  
			
			$mybooks = new WP_Query( $args ) ;
			// The Loop
			while ( $mybooks->have_posts() ) : $mybooks->the_post();
			
			echo  '<div class="featurebook">';
			//echo $hasimage.'stuff';
			
			// image

			if($hasimage=='on'){
				echo '<div class="image">';
				echo  '<a href="'.get_permalink().'">';
				echo castleimage($width,$height);
				echo  '</a>';
				echo '</div>';
			} // end if has image
			
			// title
			if($hastitle=='on'){
				echo  '<div class="name">';
				echo  '<a href="'.get_permalink().'">';
				echo  the_title();
				echo  '</a>';
				echo  '</div>';
			} // end if has title

			// excerpt
			if($hasexcerpt=='on'){	
				if (!empty($post->post_excerpt)){
					$excerpt = $post->post_excerpt;
				} else {
					$excerpt = strip_tags($post->post_content);
				}
				$excerpt = ttruncat($excerpt, $excerptlen);
					
				echo  '<div class="excerpt">';
				echo   $excerpt;

			// readmore
				echo  '<span class="readmore">';
				echo  '<a href="'.get_permalink().'">';
				echo  'Read More &raquo;';
				echo  '</a>';
				echo  '</span>';
				echo  '</div>';
			} // end if has excerpt

			if($hasexcerpt!='on'){	
				echo  '<div class="readmore">';
				echo  '<a href="'.get_permalink().'">';
				echo  'Read More &raquo;';
				echo  '</a>';
				echo  '</div>';
			}
			
			echo  '</div> <!-- End featurebook -->';
			
			endwhile;
			// Reset Post Data
			wp_reset_postdata();
			
			echo  '<div style="clear:both;">';
			//return $output;
	}
 
} // end class widget class
add_action('widgets_init', create_function('', 'return register_widget("featured_books");'));






/*------------------------------------------*/
/*  Latest Books Widget                     */
/*------------------------------------------*/
/**
* 
*/
// 
class latest_book extends WP_Widget {
    /** constructor -- name this the same as the class above */
    function latest_book() {
        parent::WP_Widget(false, $name = 'Latest Book');	
    }
    /* @see WP_Widget::widget -- do not rename this */
    function widget($args, $instance) {	
        extract( $args );
        $title 		= apply_filters('widget_title', $instance['title']);
        $message 	= apply_filters('widget_message', $instance['message']);
        $width 	= apply_filters('widget_width', $instance['width']);
        $height 	= apply_filters('widget_height', $instance['height']);

        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; ?>
							<ul>
								<?php if(!empty($message)){echo '<li><div class="message">'.$message.'</div></li>';} ?>
								<li><?php $this->get_latest_book($instance); ?></li>
							</ul>
							<div style="clear:both;"></div>
              <?php echo $after_widget; ?>
        <?php
    }
 
    /** @see WP_Widget::update -- do not rename this */
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
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {
	global $wpdb;	
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
		<p>
		Displays the latest book 
		</p>
		
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>:</label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('message'); ?>"><?php _e('Intro: (leave blank to omit)'); ?>:</label>
          <input class="widefat" id="<?php echo $this->get_field_id('message'); ?>" name="<?php echo $this->get_field_name('message'); ?>" type="text" value="<?php echo $message; ?>" />
        </p>
		<p>
          <label for="<?php echo $this->get_field_id('selectmode'); ?>"><?php _e('Choose how to select the latest book'); ?>:</label>
		  
		  <select name="<?php echo $this->get_field_name('selectmode'); ?>" id="<?php echo $this->get_field_id('selectmode'); ?>">
			<option value="">Choose Option</option>			
			<option value="manual_select"<?php selected( $selectmode, 'manual_select' ); ?>>Choose Manually</option>
			<option value="by_date"<?php selected( $selectmode, 'by_date' ); ?>>Latest by Post Date</option>
		</select>
        </p>
		
		<p>
          <label for="<?php echo $this->get_field_id('theprod'); ?>"><?php _e('Select Latest Book (for use if using manual select, above)'); ?>:</label>
		  
		  <select name="<?php echo $this->get_field_name('theprod'); ?>" id="<?php echo $this->get_field_id('theprod'); ?>">
			<option value="">Choose book</option>
			<?php
			
			
		// get all prods in options
		$sql = "SELECT ID, post_title FROM wp_posts WHERE post_type = 'min_products' AND post_status = 'publish' ORDER BY post_title ASC";
		$prods = $wpdb->get_results($sql);
	//	$wpdb->show_errors();
	//	print_r($prods);
        if ( !empty( $prods ) ){
			foreach ($prods as $prod) {	
				$theID = $prod->ID;
				$thetitle = ttruncat($prod->post_title, 20); 
				// selected( $theprod, $theID );
				echo '<option value="'.$theID.'" '.selected( $theprod, $theID ).' >'.$thetitle.'</option>';	
				
			}
		}
			
			
			?>
			
		</select>
        </p>		
		
		<p>
          <label for="<?php echo $this->get_field_id('hasimage'); ?>"><?php _e('Show Book Image'); ?>:</label> 
          <input  id="<?php echo $this->get_field_id('hasimage'); ?>" name="<?php echo $this->get_field_name('hasimage'); ?>" type="checkbox" <?php checked( $hasimage, 'on' ); ?> />
        </p>

		<p>
          <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width: (default - 200px)'); ?>:</label>
          <input class="" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" size="5" value="<?php echo $width; ?>" />
        </p>


		<p>
          <label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height: (default - 300px)'); ?>:</label>
          <input class="" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" size="5" value="<?php echo $height; ?>" />
        </p>


		
		<p>
          <label for="<?php echo $this->get_field_id('hastitle'); ?>"><?php _e('Show Book Title'); ?>:</label> 
          <input class="" id="<?php echo $this->get_field_id('hastitle'); ?>" name="<?php echo $this->get_field_name('hastitle'); ?>" type="checkbox" <?php checked( $hastitle, 'on' ); ?> />
        </p>
		
		<p>
          <label for="<?php echo $this->get_field_id('hasexcerpt'); ?>"><?php _e('Show Excerpt of Book Description'); ?>:</label> 
          <input class="" id="<?php echo $this->get_field_id('hasexcerpt'); ?>" name="<?php echo $this->get_field_name('hasexcerpt'); ?>" type="checkbox" <?php checked( $hasexcerpt, 'on' ); ?> />
        </p>
		
		<p>
          <label for="<?php echo $this->get_field_id('excerptlen'); ?>"><?php _e('Length of Book Description (in characters)'); ?>:</label> 
          <input class="" id="<?php echo $this->get_field_id('excerptlen'); ?>" name="<?php echo $this->get_field_name('excerptlen'); ?>" type="text" size="5" value="<?php echo $excerptlen; ?>" />
        </p>

        <?php 
    }
	
	function get_prod_by_date() {
		global $wpdb;
		$lateprod = $wpdb->get_row($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'min_products' ORDER BY post_date DESC"));
		$wpdb->show_errors();
        if ( !empty( $lateprod ) ){
				$theprod = $lateprod->ID;
			} else {
				$theprod = 0;
			}
		return $theprod;
	}


	function get_latest_book($instance) {

		global $post;
		    $selectmode 	= $instance['selectmode']; // number of products to show
			if(!isset($selectmode)){$selectmode='by_date';}

			$hasimage	= $instance['hasimage']; // show the image or no
			if (!isset($hasimage)){$hasimage='on';}
			
			$hastitle	= $instance['hastitle']; // show book title or no
			if (!isset($hastitle)){$hastitle='on';}
			
			$hasexcerpt	= $instance['hasexcerpt'];
			if (!isset($hasexcerpt)){$hasexcerpt='on';}
			
			$excerptlen	= $instance['excerptlen']; // number of chars to show in excerpt
			if (!isset($excerptlen)){$excerptlen=100;}

			$width	= $instance['width']; // image width
			if (!isset($width)){$width=200;}

			$height	= $instance['height']; // image height
			if (!isset($height)){$height=300;}
			
			$width = preg_replace("/[^0-9]/","", $width);  // just in case someone put in alpha chars eg - "300px"
			$height = preg_replace("/[^0-9]/","", $height);  // just in case someone put in alpha chars eg - "300px"

			//print_r($instance);
			
			// case 1 - if the selectmode is manual select
			if($selectmode == 'manual_select'){
				$latestprod = $instance['theprod']; // the product if selected manually
			}
			// case 2 - if the selectmode is by date
			else { // uses else because there are only two options and this one is default
				// get the min_product that is the latest
				$latestprod = $this->get_prod_by_date();
			}
			//print_r($latestprod);
			$dargs = array(
				'p'	=> $latestprod,
				'post_type'=>'min_products'
			);  
			
			$latestbook = new WP_Query( $dargs ) ;
			// The Loop
			while ( $latestbook->have_posts() ) : $latestbook->the_post();
			
			echo  '<div class="featurebook">';
			//echo $hasimage.'stuff';
			
			// image
			if($hasimage=='on'){
				echo '<div class="image">';
				echo  '<a href="'.get_permalink().'">';
				echo  castleimage($width,$height);
				echo  '</a>';
				echo '</div>';
			} // end if has image
			
			// title
			if($hastitle=='on'){
				echo  '<div class="name">';
				echo  '<a href="'.get_permalink().'">';
				echo  the_title();
				echo  '</a>';
				echo  '</div>';
			} // end if has title

			// excerpt
			if($hasexcerpt=='on'){	
				if (!empty($post->post_excerpt)){
					$excerpt = $post->post_excerpt;
				} else {
					$excerpt = strip_tags($post->post_content);
				}
				$excerpt = ttruncat($excerpt, $excerptlen);
					
				echo  '<div class="excerpt">';
				echo   $excerpt;

			// readmore
				echo  '<span class="readmore">';
				echo  '<a href="'.get_permalink().'">';
				echo  'Read More &raquo;';
				echo  '</a>';
				echo  '</span>';
				echo  '</div>';
			} // end if has excerpt

			if($hasexcerpt!='on'){	
				echo  '<div class="readmore">';
				echo  '<a href="'.get_permalink().'">';
				echo  'Read More &raquo;';
				echo  '</a>';
				echo  '</div>';
			}
			
			echo  '</div> <!-- End featurebook -->';
			
			endwhile;
			// Reset Post Data
			wp_reset_postdata();
			
			echo  '<div style="clear:both;">';
			//return $output;
	}
 
} // end class widget class
add_action('widgets_init', create_function('', 'return register_widget("latest_book");'));
?>