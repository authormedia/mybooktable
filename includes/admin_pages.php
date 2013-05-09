<?php

function mbt_admin_pages_init() {
	add_action('admin_menu', 'mbt_add_admin_pages', 9);
	add_action('admin_enqueue_scripts', 'mbt_load_admin_style');
}
add_action('mbt_init', 'mbt_admin_pages_init');

function mbt_load_admin_style() {
	wp_register_style('mbt_admin_css', plugins_url('css/admin-style.css', dirname(__FILE__)));
	wp_enqueue_style('mbt_admin_css');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_style('jquery-ui', plugins_url('css/jquery-ui.css', dirname(__FILE__)));
}

function mbt_add_admin_pages() {
	add_menu_page("MyBookTable", "MyBookTable", 'edit_posts', "mbt_landing_page", 'mbt_render_landing_page', plugins_url('images/icon.png', dirname(__FILE__)), 10);
	add_submenu_page("mbt_landing_page", "Books", "Books", 'edit_posts', "edit.php?post_type=mbt_book");
	add_submenu_page("mbt_landing_page", "Authors", "Authors", 'edit_posts', "edit-tags.php?taxonomy=mbt_author");
	add_submenu_page("mbt_landing_page", "Genres", "Genres", 'edit_posts', "edit-tags.php?taxonomy=mbt_genre");
	add_submenu_page("mbt_landing_page", "Series", "Series", 'edit_posts', "edit-tags.php?taxonomy=mbt_series");
	add_submenu_page("mbt_landing_page", "MyBookTable Settings", "Settings", 'manage_options', "mbt_settings", 'mbt_render_settings_page');
	add_submenu_page("mbt_landing_page", "MyBookTable Help", "Help", 'edit_posts', "mbt_help", 'mbt_render_help_page');

	remove_menu_page("edit.php?post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit.php?post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "post-new.php?post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_author&amp;post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_genre&amp;post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_series&amp;post_type=mbt_book");
}

function mbt_render_settings_page() {
	if(isset($_REQUEST['save_settings'])) {
		do_action("mbt_buybutton_settings_save");
		mbt_update_setting('booktable_page', $_REQUEST['mbt_booktable_page']);
		mbt_update_setting('buybutton_style', $_REQUEST['mbt_buybutton_style']);
		mbt_update_setting('series_in_excerpts', isset($_REQUEST['mbt_series_in_excerpts'])?true:false);
		mbt_update_setting('socialmedia_in_excerpts', isset($_REQUEST['mbt_socialmedia_in_excerpts'])?true:false);
		mbt_update_setting('posts_per_page', $_REQUEST['mbt_posts_per_page']);
		mbt_update_setting('disable_seo', isset($_REQUEST['mbt_disable_seo'])?true:false);
		mbt_update_setting('disable_socialmedia', isset($_REQUEST['mbt_disable_socialmedia'])?true:false);
		mbt_update_setting('featured_buybuttons', isset($_REQUEST['mbt_featured_buybuttons'])?$_REQUEST['mbt_featured_buybuttons']:array());

		$settings_updated = true;
	}

	?>

	<script>
		jQuery(document).ready(function() {
			jQuery("#mbt-tabs").tabs({active: <?php echo(isset($_REQUEST['tab'])?$_REQUEST['tab']:0); ?>});
		});
	</script>

	<div class="wrap mbt_settings">
		<div id="icon-options-general" class="icon32"><br></div><h2>MyBookTable Settings</h2>
		<?php if(isset($settings_updated)) { ?>
			<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved.</strong></p></div>
		<?php } ?>

		<form id="mbt_settings_form" method="post" action="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>">

			<div id="mbt-tabs">
				<ul>
					<li><a href="#tabs-1">General Settings</a></li>
					<li><a href="#tabs-2">Buy Button Settings</a></li>
					<li><a href="#tabs-3">Book Listings Settings</a></li>
					<li><a href="#tabs-4">Uninstall</a></li>
				</ul>
				<div id="tabs-1">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="blogname">Booktable Page</label></th>
								<td>
									<select name="mbt_booktable_page" id="mbt_booktable_page">
										<option value="0" <?php echo(mbt_get_setting('booktable_page') <= 0 ? ' selected="selected"' : '') ?> > -- Choose One -- </option>
										<?php foreach(get_pages() as $page) { ?>
											<option value="<?php echo($page->ID); ?>" <?php echo(mbt_get_setting('booktable_page') == $page->ID ? ' selected="selected"' : ''); ?> ><?php echo($page->post_title); ?></option>
										<?php } ?>
									</select>
									<?php if(mbt_get_setting('booktable_page') <= 0) { ?>
										<a href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_install_pages=1')); ?>" id="submit" class="button button-primary">Click here to create a booktable page</a>
									<?php } ?>
									<p class="description">The Booktable page is the main landing page for your books, it must have the [mbt_booktable] shortcode.</p>
								</td>
							</tr>
							<?php if(!mbt_get_setting('installed_examples')) { ?>
								<tr valign="top">
									<th scope="row">Example Books</th>
									<td>
										<a href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_install_examples=1')); ?>" id="submit" class="button button-primary">Click here to create example books</a>
										<p class="description">These examples will help you learn how to set up Genres, Series, Authors, and Books of your own.</p>
									</td>
								</tr>
							<?php } ?>
							<tr valign="top">
								<th scope="row"><label for="mbt_disable_seo">Disable SEO</label></th>
								<td>
									<input type="checkbox" name="mbt_disable_seo" id="mbt_disable_seo" <?php echo(mbt_get_setting('disable_seo') ? ' checked="checked"' : ''); ?> >
									<p class="description">Check to disable MyBookTable's built-in SEO features.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_disable_seo">Disable Social Media</label></th>
								<td>
									<input type="checkbox" name="mbt_disable_socialmedia" id="mbt_disable_socialmedia" <?php echo(mbt_get_setting('disable_socialmedia') ? ' checked="checked"' : ''); ?> >
									<p class="description">Check to disable MyBookTable's built-in social media features.</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=0');"></p>
				</div>
				<div id="tabs-2">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="mbt_buybutton_style">Buy Buttons Style</label></th>
								<td>
									<select name="mbt_buybutton_style" id="mbt_buybutton_style">
										<?php $style = mbt_get_setting('buybutton_style'); ?>
										<option value="default" <?php echo((empty($style) or $style == 'default') ? ' selected="selected"' : '') ?> >Default</option>
										<?php /*foreach(mbt_get_buybutton_styles() as $style) { ?>


											<?php <option value="<?php echo($page->ID); ?>" <?php echo(mbt_get_setting('booktable_page') == $page->ID ? ' selected="selected"' : ''); ?> ><?php echo($page->post_title); ?></option> ?>


										<?php }*/ ?>
									</select>
									<p class="description">Choose the style pack you would like for your buttons.</p>
								</td>
							</tr>
						</tbody>
					</table>
					<?php do_action("mbt_buybutton_settings"); ?>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=1');"></p>
				</div>
				<div id="tabs-3">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="mbt_series_in_excerpts">Show other books in the same Series in excerpts</label></th>
								<td>
									<input type="checkbox" name="mbt_series_in_excerpts" id="mbt_series_in_excerpts" <?php echo(mbt_get_setting('series_in_excerpts') ? ' checked="checked"' : ''); ?> >
									<p class="description">If checked, the related books will display in book excerpts in book listings.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_series_in_excerpts">Show social media buttons in excerpts</label></th>
								<td>
									<input type="checkbox" name="mbt_socialmedia_in_excerpts" id="mbt_socialmedia_in_excerpts" <?php echo(mbt_get_setting('socialmedia_in_excerpts') ? ' checked="checked"' : ''); ?> >
									<p class="description">If checked, the social media buttons will display in book excerpts in book listings.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_posts_per_page">Number of Books per Page</label></th>
								<td>
									<input name="mbt_posts_per_page" type="text" id="mbt_posts_per_page" value="<?php echo(mbt_get_setting('posts_per_page') ? mbt_get_setting('posts_per_page') : get_option('posts_per_page')); ?>" class="regular-text">
									<p class="description">Choose the number of books to show per page on the book listings.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="blogname">Featured Buy Buttons</label></th>
								<td>
									<?php
										$mbt_featured_buybuttons = mbt_get_setting('featured_buybuttons');
										foreach(mbt_get_buybuttons() as $type=>$button) {
											echo('<input type="checkbox" name="mbt_featured_buybuttons['.$type.']" '.(isset($mbt_featured_buybuttons[$type]) ? ' checked="checked"' : '').' > '.$button['name'].'<br>');
										}
									?>
									<p class="description">Featured Buy Buttons show up on book listings.</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=2');"></p>
				</div>
				<div id="tabs-4">
					<p class="submit"><a href="<?php echo(admin_url('plugins.php?mbt_uninstall=1')); ?>" type="submit" name="save_settings" id="submit" class="button button-primary">Uninstall MyBookTable</a></p>
					<p class="description">Use this to completely uninstall all MyBookTable settings, books, series, genres, and authors. WARNING: THIS IS PERMANENT.</p>
				</div>
			</div>

		</form>

	</div>

<?php
}

function mbt_render_help_page() {
?>

	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div><h2>MyBookTable Help</h2>

		<h3>How do I kittens?</h3>
		<iframe width="640" height="360" src="https://www.youtube.com/embed/gppbrYIcR80?feature=player_detailpage" frameborder="0" allowfullscreen></iframe>
		<p>In this video we describe how to blah blah blah</p>

		<h3>How do I puppies?</h3>
		<iframe width="640" height="360" src="https://www.youtube.com/embed/5L28TM48bF0?feature=player_detailpage" frameborder="0" allowfullscreen></iframe>
		<p>In this video we describe how to blah blah blah</p>

	</div>

<?php
}

function mbt_render_landing_page() {
?>

	<div class="wrap mbt-landing-page">
		<div id="icon-index" class="icon32"><br></div><h2>MyBookTable</h2>
		<div class="welcome-video-container">
			<div class="welcome-video welcome-panel">
				<iframe width="640" height="360" src="https://www.youtube.com/embed/gppbrYIcR80?feature=player_detailpage" frameborder="0" allowfullscreen></iframe><br>
				<a href="<?php echo(admin_url('admin.php?page=mbt_help')); ?>">More Tutorial Videos</a>
			</div>
		</div>

		<div class="buttons-container">
			<a href="<?php echo(admin_url('post-new.php?post_type=mbt_book')); ?>" class="add-new-book">Add New Book</a>
		</div>

		<div id="welcome-panel" class="welcome-panel">
			<div class="welcome-panel-content">
				<h3>Welcome to MyBookTable!</h3>
				<p class="about-description">We’ve assembled some links to get you started:</p>
				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column">
						<h4>Next Steps</h4>
						<ul>
							<?php if(!mbt_get_setting('installed_examples')) { ?>
								<li><a href="<?php echo(admin_url('edit.php?post_type=mbt_book&mbt_install_examples=1')); ?>" class="welcome-icon">Look at some example Books</a></li>
							<?php } ?>
							<li><a href="<?php echo(admin_url('post-new.php?post_type=mbt_book')); ?>" class="welcome-icon welcome-add-page">Create your first book</a></li>
							<?php if(mbt_get_setting('booktable_page')) { ?>
								<li><a href="<?php echo(get_permalink(mbt_get_setting('booktable_page'))); ?>" class="welcome-icon welcome-view-site">View your Book Table</a></li>
							<?php } ?>
						</ul>
					</div>
					<div class="welcome-panel-column welcome-panel-last">
						<h4>More Actions</h4>
						<ul>
							<li><div class="welcome-icon welcome-widgets-menus">Manage <a href="<?php echo(admin_url('edit.php?post_type=mbt_book')); ?>">Books</a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus">Manage <a href="<?php echo(admin_url('edit-tags.php?taxonomy=mbt_author')); ?>">Authors</a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus">Manage <a href="<?php echo(admin_url('edit-tags.php?taxonomy=mbt_genre')); ?>">Genres</a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus">Manage <a href="<?php echo(admin_url('edit-tags.php?taxonomy=mbt_series')); ?>">Series</a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus">Manage <a href="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>">Settings</a></div></li>
							<li><a href="<?php echo(admin_url('admin.php?page=mbt_help')); ?>" class="welcome-icon welcome-learn-more">Learn more about MyBookTable</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<div class="metabox-holder">
			<div id="mbt_dashboard_rss" class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><span>Recent News from Author Media</span></h3>
				<div class="inside">
					<?php wp_widget_rss_output(array(
						'link' => 'http://www.authormedia.com/',
						'url' => 'http://www.authormedia.com/feed/',
						'title' => 'Recent News from Author Media',
						'items' => 3,
						'show_summary' => 1,
						'show_author' => 0,
						'show_date' => 1,
					)); ?>
				</div>
			</div>
		</div>

	</div>

<?php
}



/*---------------------------------------------------------*/
/* Custom Images for Taxonomies                            */
/*---------------------------------------------------------*/

add_filter('mbt_author_edit_form_fields', 'mbt_add_taxonomy_image_edit_form');
add_filter('mbt_author_add_form_fields', 'mbt_add_taxonomy_image_add_form');
add_action('edited_mbt_author', 'mbt_save_taxonomy_image_edit_form');
add_action('created_mbt_author', 'mbt_save_taxonomy_image_add_form');

add_filter('mbt_genre_edit_form_fields', 'mbt_add_taxonomy_image_edit_form');
add_filter('mbt_genre_add_form_fields', 'mbt_add_taxonomy_image_add_form');
add_action('edited_mbt_genre', 'mbt_save_taxonomy_image_edit_form');
add_action('created_mbt_genre', 'mbt_save_taxonomy_image_add_form');

add_filter('mbt_series_edit_form_fields', 'mbt_add_taxonomy_image_edit_form');
add_filter('mbt_series_add_form_fields', 'mbt_add_taxonomy_image_add_form');
add_action('edited_mbt_series', 'mbt_save_taxonomy_image_edit_form');
add_action('created_mbt_series', 'mbt_save_taxonomy_image_add_form');

function mbt_add_taxonomy_image_edit_form() {
?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="mbt_tax_image_url">Image</label></th>
		<td>
			<input type="text" id="mbt_tax_image_url" name="mbt_tax_image_url" value="<?php echo(mbt_get_taxonomy_image($_REQUEST['taxonomy'], $_REQUEST['tag_ID'])); ?>" />
    		<input id="mbt_upload_tax_image_button" type="button" class="button" value="Upload" />
        </td>
	</tr>
<?php
}

function mbt_add_taxonomy_image_add_form() {
?>
	<div class="form-field">
		<label for="mbt_tax_image_url">Image</label>
		<input type="text" id="mbt_tax_image_url" name="mbt_tax_image_url" value="" />
		<input id="mbt_upload_tax_image_button" type="button" class="button" value="Upload" />
	</div>
<?php
}

function mbt_save_taxonomy_image_edit_form() {
	mbt_save_taxonomy_image($_REQUEST['taxonomy'], $_REQUEST['tag_ID'], $_REQUEST['mbt_tax_image_url']);
}

function mbt_save_taxonomy_image_add_form($term_id) {
	mbt_save_taxonomy_image($_REQUEST['taxonomy'], $term_id, $_REQUEST['mbt_tax_image_url']);
}