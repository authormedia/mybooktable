<?php

function mbt_shorcodes_init() {
	add_shortcode('mybooktable', 'mbt_mybooktable_shortcode');
	add_action('mbt_render_help_page', 'mbt_render_shordcode_help');
	add_filter('authormedia_get_shortcodes', 'mbt_add_authormedia_shortcodes');
}
add_action('mbt_init', 'mbt_shorcodes_init');

function mbt_add_authormedia_shortcodes($shortcodes) {
	function mbt_get_taxonomy_names($tax) {
		$names = array();
		$terms = get_terms($tax);
		foreach($terms as $term) {
			$names[$term->slug] = $term->name;
		}
		return $names;
	}

	$books = array();
	$books_query = new WP_Query(array('post_type' => 'mbt_book', 'posts_per_page' => -1));
	foreach($books_query->posts as $book) {
		$books[$book->post_name] = $book->post_title;
	}

	//Add default shortcodes
	$shortcodes['mybooktable'] = array('name' => 'MyBookTable', 'shortcodes' => array(
		'mybooktable' => array(
			'title'			=> __('All Books', 'mybooktable'),
			'description'	=> __('List all your books in an embedded book listing.', 'mybooktable'),
		),
		'mybooktable-series' => array(
			'title'			=> __('All Books in Series', 'mybooktable'),
			'description'	=> __('List all the books in a given series in an embedded book listing.', 'mybooktable'),
			'settings'		=> array(
				'series'	=> array(
					'title'			=> __('Series', 'mybooktable'),
					'description'	=> '',
					'type'			=> 'dropdown',
					'choices'		=> mbt_get_taxonomy_names('mbt_series')
				)
			)
		),
		'mybooktable-genre' => array(
			'title'			=> __('All Books in Genre', 'mybooktable'),
			'description'	=> __('List all the books in a given genre in an embedded book listing.', 'mybooktable'),
			'settings'		=> array(
				'genre'	=> array(
					'title'			=> __('Genre', 'mybooktable'),
					'description'	=> '',
					'type'			=> 'dropdown',
					'choices'		=> mbt_get_taxonomy_names('mbt_genre')
				)
			)
		),
		'mybooktable-tag' => array(
			'title'			=> __('All Books with Tag', 'mybooktable'),
			'description'	=> __('List all the books with a given tag in an embedded book listing.', 'mybooktable'),
			'settings'		=> array(
				'tag'	=> array(
					'title'			=> __('Tag', 'mybooktable'),
					'description'	=> '',
					'type'			=> 'dropdown',
					'choices'		=> mbt_get_taxonomy_names('mbt_tag')
				)
			)
		),
		'mybooktable-author' => array(
			'title'			=> __('All Books by Author', 'mybooktable'),
			'description'	=> __('List all the books written by a given author in an embedded book listing.', 'mybooktable'),
			'settings'		=> array(
				'author'	=> array(
					'title'			=> __('Author', 'mybooktable'),
					'description'	=> '',
					'type'			=> 'dropdown',
					'choices'		=> mbt_get_taxonomy_names('mbt_author')
				)
			)
		),
		'mybooktable-book' => array(
			'title'			=> __('Single Book', 'mybooktable'),
			'description'	=> __('Show a given book in an embedded book listing.', 'mybooktable'),
			'settings'		=> array(
				'book'	=> array(
					'title'			=> __('Book', 'mybooktable'),
					'description'	=> '',
					'type'			=> 'dropdown',
					'choices'		=> $books
				),
				'display'	=> array(
					'title'			=> __('Display Style', 'mybooktable'),
					'description'	=> '',
					'type'			=> 'dropdown',
					'choices'		=> array("default" => __("Default", 'mybooktable'), "summary" => __("Summary", 'mybooktable'))
				)
			)
		),
		'mybooktable-list' => array(
			'title'			=> __('All Terms in Taxonomy', 'mybooktable'),
			'description'	=> __('This allows you to display all of the different items in a MyBookTable taxonomy.', 'mybooktable'),
			'settings'		=> array(
				'list'	=> array(
					'title'			=> __('Taxonomy', 'mybooktable'),
					'description'	=> '',
					'type'			=> 'dropdown',
					'choices'		=> array("series" => __("Series", 'mybooktable'), "genres" => __("Genres", 'mybooktable'), "tags" => __("Tags", 'mybooktable'), "authors" => __("Authors", 'mybooktable'))
				),
				'display'	=> array(
					'title'			=> __('Display Style', 'mybooktable'),
					'description'	=> '',
					'type'			=> 'dropdown',
					'choices'		=> array("listing" => __("Listing", 'mybooktable'), "bar" => __("Menu Bar", 'mybooktable'), "simple" => __("Simple", 'mybooktable'))
				)
			)
		)
	));

	return $shortcodes;
}



/*---------------------------------------------------------*/
/* Shortcodes                                              */
/*---------------------------------------------------------*/

function mbt_mybooktable_shortcode($attrs) {
	global $wp_query, $posts, $post, $id, $mbt_in_custom_page_content;

	if(!empty($mbt_in_custom_page_content)) { return ''; }
	if(mbt_is_mbt_page()) { return ''; }

	$output = '';
	if(!empty($attrs['list'])) {
		if($attrs['list'] == 'authors') {
			$tax = 'mbt_author';
		} else if($attrs['list'] == 'series') {
			$tax = 'mbt_series';
		} else if($attrs['list'] == 'genres') {
			$tax = 'mbt_genre';
		} else if($attrs['list'] == 'tags') {
			$tax = 'mbt_tag';
		} else {
			return '';
		}

		$display = empty($attrs['display']) ? '' : $attrs['display'];

		if($display == "simple" || $display == "bar") {
			$output .= '<ul class="mbt-taxonomy-list '.$display.'">';

			$terms = get_terms($tax);
			foreach($terms as $term) {
				$link = get_term_link($term);
				$name = $term->name;

				$output .= '<li class="mbt-taxonomy-item"><a href="'.$link.'">'.$name.'</a></li>';
			}

			$output .= '	<div style="clear:both;"></div>';
			$output .= '</ul>';
		} else {
			$output .= '<div id="mbt-container">';
			$output .= '	<div class="mbt-taxonomy-listing">';

			$terms = get_terms($tax);
			foreach($terms as $term) {
				$link = get_term_link($term);
				$name = $term->name;
				$desc = $term->description;
				$img = mbt_get_taxonomy_image($term->taxonomy, $term->term_id);

				$output .= '<div class="mbt-taxonomy">';
				$output .= '	<div class="mbt-taxonomy-image">';
				$output .= '		<a href="'.$link.'"><img class="mbt-taxonomy-image" src="'.$img.'"></a>';
				$output .= '	</div>';
				$output .= '	<div class="mbt-taxonomy-right">';
				$output .= '		<h1 class="mbt-taxonomy-title"><a href="'.$link.'">'.$name.'</a></h1>';
				if(!empty($desc)) { $output .= '		<div class="mbt-taxonomy-description">'.$desc.'</div>'; }
				$output .= '	</div>';
				$output .= '	<div style="clear:both;"></div>';
				$output .= '</div>';

			}

			$output .= '	</div>';
			$output .= '</div>';
		}
	} else {

		$max_books = empty($attr['max-books']) ? -1 : $attr['max-books'];

		$mbt_shortcode_old_id = $id;
		$mbt_shortcode_old_post = $post;
		$mbt_shortcode_old_posts = $posts;
		$mbt_shortcode_old_wp_query = $wp_query;
		if(!empty($attrs['book'])) {
			$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'name' => $attrs['book']));
		} else if(!empty($attrs['author'])) {
			$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'mbt_author' => $attrs['author'], 'orderby' => 'menu_order', 'posts_per_page' => $max_books));
		} else if(!empty($attrs['series'])) {
			$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'mbt_series' => $attrs['series'], 'orderby' => 'menu_order', 'posts_per_page' => $max_books));
		} else if(!empty($attrs['genre'])) {
			$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'mbt_genre' => $attrs['genre'], 'orderby' => 'menu_order', 'posts_per_page' => $max_books));
		} else if(!empty($attrs['tag'])) {
			$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'mbt_tag' => $attrs['tag'], 'orderby' => 'menu_order', 'posts_per_page' => $max_books));
		} else {
			$wp_query = new WP_Query(array('post_type' => 'mbt_book', 'orderby' => 'menu_order', 'posts_per_page' => $max_books));
		}
		$post = empty($wp_query->post) ? null : $wp_query->post;
		$posts = $wp_query->posts;

		ob_start();

		$mbt_in_custom_page_content = true;
		if(is_singular('mbt_book')) {
			if(!empty($attrs['display']) and $attrs['display'] == 'summary') {
				echo('<div id="mbt-container">');
				include(mbt_locate_template('excerpt-book.php'));
				echo('</div>');
			} else {
				remove_action('mbt_before_single_book', 'mbt_the_breadcrumbs');
				include(mbt_locate_template('single-book/content.php'));
			}
		} else {
			remove_action('mbt_before_book_archive', 'mbt_the_breadcrumbs');
			include(mbt_locate_template('archive-book/content.php'));
		}
		$mbt_in_custom_page_content = false;

		$output = ob_get_contents();
		ob_end_clean();

		$wp_query = $mbt_shortcode_old_wp_query;
		$posts = $mbt_shortcode_old_posts;
		$post = $mbt_shortcode_old_post;
		$id = $mbt_shortcode_old_id;
	}

	return $output;
}



/*---------------------------------------------------------*/
/* Help Page                                               */
/*---------------------------------------------------------*/

function mbt_render_shordcode_help() {
_e('
	<br><br><h2>MyBookTable Shortcodes</h2>
	<p>MyBookTable has a single [mybooktable] shortcode that can be used for a variety of purposes:</p>

	<h3>List all your books</h3>
	<pre>[mybooktable]</pre>

	<h3>List the books in a series</h3>
	<pre>[mybooktable series="lordoftherings"]</pre>
	<p>Note that you must use the <strong>slug</strong> of the series, not the name.<p/>

	<h3>List the books in a genre</h3>
	<pre>[mybooktable genre="fantasy"]</pre>
	<p>Note that you must use the <strong>slug</strong> of the genre, not the name.<p/>

	<h3>List the books with a given tag</h3>
	<pre>[mybooktable tag="recommended"]</pre>
	<p>Note that you must use the <strong>slug</strong> of the tag, not the name.<p/>

	<h3>List the books written by an author</h3>
	<pre>[mybooktable author="jrrtolkien"]</pre>
	<p>Note that you must use the <strong>slug</strong> of the author, not the name.<p/>

	<h3>Display a single book</h3>
	<pre>[mybooktable book="the-fellowship-of-the-ring"]</pre>
	<p>Note that you must use the <strong>slug</strong> of the book, not the name.<p/>

	<h3>Display a single book summary</h3>
	<pre>[mybooktable book="the-fellowship-of-the-ring" display="summary"]</pre>

	<h3>Display a list of taxonomy terms</h3>
	<pre>[mybooktable list="taxonomy" display="listing"]</pre>
	<p>
		This allows you to display all of the different items in a MyBookTable taxonomy, such as all the authors or all the genres.
		The valid options for the "list" field are "authors", "series", "genres", and "tags". The valid options for the "display" field are "listing", "bar", and "simple".
	<p/>
', 'mybooktable');
}



/*---------------------------------------------------------*/
/* Author Media Shortcode Inserter                         */
/*---------------------------------------------------------*/

//This shortcode inserter code is adapted from the GetNoticed_Shortcode_Controls in the GetNoticed Theme licenced under the GNU General Public License (http://getnoticedtheme.com/)

if(!function_exists('authormedia_setup_shortcode_inserter')) {
	add_action('admin_init', 'authormedia_setup_shortcode_inserter');

	function authormedia_setup_shortcode_inserter() {
		if((current_user_can('edit_posts') || current_user_can('edit_pages')) && get_user_option('rich_editing') == 'true') {
			if(in_array(basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php'))) {
				add_filter('media_buttons', 'authormedia_shortcode_inserter_button', 30);
				add_action('admin_footer', 'authormedia_shortcode_inserter_form');
			}
		}
	}

	function authormedia_shortcode_inserter_button($buttons) {
		echo '<a href="#TB_inline?width=480&inlineId=select_authormedia_shortcode" class="thickbox button authormedia_shortcode_button"><span class="authormedia_shortcode_icon"></span>'.__('Insert Shortcode', 'mybooktable').'</a>';
	}

	function authormedia_shortcode_inserter_form() {
		$shortcode_sections = apply_filters('authormedia_get_shortcodes', array());
		?>
		<script type="text/javascript">
			function authormedia_insert_shortcode() {
				var shortcode = jQuery('.authormedia-shortcode-section .shortcode-menu-item.active').data('shortcode');
				if(shortcode == '') {
					alert('<?php _e("Please select a shortcode.") ?>', 'mybooktable');
					return;
				}

				shortcode_tag = shortcode.split('-')[0];

				var attrs = {};
				jQuery('#authormedia_shortcode_form_' + shortcode + ' .authormedia_shortcode_field').each( function(){
					if ( 'checkbox' == jQuery(this).attr('type') ) {
						attrs[ jQuery(this).attr('name') ] = jQuery(this).is(':checked');
					} else if ( 'radio' == jQuery(this).attr('type') ) {
						if ( jQuery(this).is(':checked') ) attrs[ jQuery(this).attr('name') ] = jQuery(this).val();
					} else if ( jQuery(this).val() ) {
						attrs[ jQuery(this).attr('name') ] = jQuery(this).val();
					}
				});
				if ( attrs["content"] > "" ) {
					var setcontent = attrs["content"];
					delete( attrs["content"] );
					shortcode = new wp.shortcode({
						tag: shortcode_tag,
						attrs: attrs,
						type: 'closed',
						content: setcontent
					});
				} else {
					shortcode = new wp.shortcode({
						tag: shortcode_tag,
						attrs: attrs,
						type: 'single'
					});
				}

				if ( window.send_to_editor )
					window.send_to_editor( shortcode.string() );
			}

			jQuery(function($) {

				$('.shortcode-modal-close').on( 'click', function(e){
					e.preventDefault();
					tb_remove();
				});

				$('.authormedia-shortcode-section .shortcode-menu-item').on( 'click', function() {
					$('.authormedia-shortcode-section .shortcode-menu-item').removeClass('active');
					$(this).addClass( 'active' );
					$('.authormedia_shortcode_form_atts').css( 'display', 'none' );
					$('#authormedia_shortcode_form_' + $(this).data('shortcode') ).css( 'display', 'block' );
				});

				$('.authormedia-shortcode-section-nav .nav-tab-wrapper a').on( 'click', function() {
					$('.authormedia-shortcode-section-nav .nav-tab-wrapper a').removeClass('nav-tab-active');
					$(this).addClass( 'nav-tab-active' );
					$('.authormedia-shortcode-section').css( 'display', 'none' );
					$('#authormedia_shortcode_section_' + $(this).attr('data-shortcode-section') ).css( 'display', 'block' );
				});
				$('.authormedia-shortcode-section-nav .nav-tab-wrapper a')[0].click();

			});
		</script>

		<div id="select_authormedia_shortcode" style="display:none;">
			<a class="media-modal-close shortcode-modal-close" href="#" title="<?php esc_attr_e('Close', 'mybooktable'); ?>">
				<span class="media-modal-icon"></span>
			</a>
			<div class="authormedia-shortcode-section-nav">
				<h2 class="nav-tab-wrapper">
					<?php
						foreach($shortcode_sections as $section_id => $section) {
							 echo('<a href="#" class="nav-tab" data-shortcode-section="'.esc_attr($section_id).'">'.$section['name'].'</a>');
						}
					?>
				</h2>
			</div>

			<?php foreach($shortcode_sections as $section_id => $section) { ?>
				<?php $shortcodes = $section['shortcodes']; ?>
				<div class="media-modal-content authormedia-shortcode-section" id="authormedia_shortcode_section_<?php echo(esc_attr($section_id)); ?>">
					<div class="media-frame wp-core-ui">
						<div class="media-frame-menu">
							<div class="media-menu">
								<?php
									foreach ( $shortcodes as $shortcode => $atts ) {
										echo '<a href="#" class="media-menu-item shortcode-menu-item" data-shortcode="' . esc_attr( $shortcode ) . '">' . esc_html( $atts['title'] ) . "</a>";
									}
								?>
							</div>
						</div>
						<div class="media-frame-title">
							<h1><?php _e('Insert a Shortcode', 'mybooktable'); ?></h1>
						</div>
						<div class="media-frame-router"></div>
						<div class="media-frame-content">
							<div id="authormedia_shortcode_form_intro" class="authormedia_shortcode_form_atts">
								<?php _e('To get started, select a shortcode from the list on the left.', 'mybooktable'); ?>
							</div>
							<?php foreach ( $shortcodes as $shortcode => $atts ): ?>
							<div id="authormedia_shortcode_form_<?php echo $shortcode; ?>" class="authormedia_shortcode_form_atts" style="display:none">
								<?php if ( !empty($atts['description']) ) { ?>
									<div class="authormedia_shortcode_description">
										<?php echo esc_html($atts['description']); ?>
									</div>
								<?php } ?>
								<?php if ( empty($atts['settings']) ) { ?>
									<div style="margin:1em">This shortcode has no options, you can insert it directly.</div>
								<?php } else { ?>
									<?php foreach ( $atts['settings'] as $setting => $params ) {
										echo '<div style="margin:1em">';
										switch ( $params['type'] ) {
											case 'dropdown':
												global $_wp_additional_image_sizes;
												if ( ! empty($params['title']) ) echo "<label for='authormedia_{$shortcode}_field_$setting'>$params[title]</label><br>";
												if ( ! empty($params['description']) ) echo '<div class="description">' . $params['description'] . '</div>';
												if ( ! empty($params['choices']) ) {
													echo "<select class='authormedia_shortcode_field' id='authormedia_{$shortcode}_field_$setting' name='$setting' style='max-width:440px;'>";
													foreach ( $params['choices'] as $slug => $name ) {
														echo "<option value='$slug'>$name</option>";
													}
													echo "</select>";
												}
												break;
											case 'thumbsize':
												global $_wp_additional_image_sizes;
												if ( ! empty($params['title']) ) echo "<label for='authormedia_{$shortcode}_field_$setting'>$params[title]</label><br>";
												if ( ! empty($params['description']) ) echo '<div class="description">' . $params['description'] . '</div>';
												echo "<select class='authormedia_shortcode_field' id='authormedia_{$shortcode}_field_$setting' name='$setting'>";
												echo "<option value=''>(default)</option>";
												foreach ( $_wp_additional_image_sizes as $name => $atts ) {
													echo "<option value='$name'>$name ($atts[width] x $atts[height])</option>";
												}
												echo "</select>";
												break;
											case 'checkboxes':
												#!! we need to output a list of checkboxes and on saving, comma-delimit them
												break;
											case 'checkbox':
												if ( ! empty($params['description']) ) echo '<div class="description">' . $params['description'] . '</div>';
												echo "<input type='checkbox' class='authormedia_shortcode_field' id='authormedia_{$shortcode}_field_$setting' name='$setting'>";
												if ( ! empty($params['title']) ) echo " <label for='authormedia_{$shortcode}_field_$setting'>$params[title]</label>";
												break;
											case 'radio':
												if ( ! empty($params['title']) ) echo $params['title'];
												if ( ! empty($params['description']) ) echo '<div class="description">' . $params['description'] . '</div>';
												if ( ! empty($params['choices']) ) {
													echo '<ul style="margin-left:2em">';
													foreach( $params['choices'] as $key => $value ) {
														echo "<li><input type='radio' class='authormedia_shortcode_field' id='authormedia_{$shortcode}_field_{$setting}_{$key}' name='$setting' value='$key'>";
														echo " <label for='authormedia_{$shortcode}_field_{$setting}_{$key}'>$value</label></li>";
													}
													echo '</ul>';
												}
												break;
											case 'content':
											case 'textarea':
												if ( ! empty($params['title']) ) {
													echo "<label for='authormedia_{$shortcode}_field_$setting'>$params[title]";
													if ( ! empty($params['default']) ) echo " <em>(default: $params[default])</em>";
													echo "</label><br>";
												}
												if ( ! empty($params['description']) ) echo '<div class="description">' . $params['description'] . '</div>';
												echo "<textarea class='authormedia_shortcode_field' id='authormedia_{$shortcode}_field_$setting' name='$setting' rows='5' cols='40'></textarea>";
												break;
											case 'text':
												if ( ! empty($params['title']) ) {
													echo "<label for='authormedia_{$shortcode}_field_$setting'>$params[title]";
													if ( ! empty($params['default']) ) echo " <em>(default: $params[default])</em>";
													echo "</label><br>";
												}
												if ( ! empty($params['description']) ) echo '<div class="description">' . $params['description'] . '</div>';
												echo "<input type='text' class='authormedia_shortcode_field' id='authormedia_{$shortcode}_field_$setting' name='$setting'>";
												break;
											case 'number':
												if ( ! empty($params['title']) ) {
													echo "<label for='authormedia_{$shortcode}_field_$setting'>$params[title]";
													if ( ! empty($params['default']) ) echo " <em>(default: $params[default])</em>";
													echo "</label><br>";
												}
												if ( ! empty($params['description']) ) echo '<div class="description">' . $params['description'] . '</div>';
												echo "<input type='text' class='authormedia_shortcode_field' id='authormedia_{$shortcode}_field_$setting' name='$setting'>";
												break;
											case '':
											default:
												if ( ! empty($params['title']) ) {
													echo "<label for='authormedia_shortcode_field_$setting'>$params[title]";
													if ( ! empty($params['default']) ) echo " <em>(default: $params[default])</em>";
													echo "</label><br>";
												}
												if ( ! empty($params['description']) ) echo '<div class="description">' . $params['description'] . '</div>';
												echo 'input type="' . $params['type'] . '" name="' . $setting . '"';
										}
										echo '</div>';
									} ?>
								<?php } ?>
							</div>
							<?php endforeach; ?>
						</div>
						<div class="media-frame-toolbar"><div class="media-toolbar">
							<div class="media-toolbar-secondary">
								<a class="button media-button button-large button-cancel" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", 'mybooktable'); ?></a>
							</div>
							<div class="media-toolbar-primary">
								<input type="button" class="button media-button button-primary button-large button-insert" value="<?php _e('Insert Shortcode', 'mybooktable'); ?>" onclick="authormedia_insert_shortcode();"/>
							</div>
						</div></div>
					</div>
				</div>
			<?php } ?>
		</div>

		<?php
	}
}
