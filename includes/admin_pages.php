<?php

//enqueue backend plugin styles and javascript
function mbt_load_admin_style() {
	wp_register_style('mbt_admin_css', plugins_url('css/admin-style.css', dirname(__FILE__)));
	wp_enqueue_style('mbt_admin_css');
	wp_enqueue_script('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js', array('jquery'), '1.9.2');
}
add_action('admin_enqueue_scripts', 'mbt_load_admin_style');

function mbt_add_admin_pages() {
	add_menu_page("MyBookTable", "MyBookTable", 'edit_posts', "mbt_landing_page", 'mbt_render_landing_page', plugins_url('images/icon.png', dirname(__FILE__)), 10);
	add_submenu_page("mbt_landing_page", "Books", "Books", 'edit_posts', "edit.php?post_type=mbt_books");
	add_submenu_page("mbt_landing_page", "Authors", "Authors", 'edit_posts', "edit-tags.php?taxonomy=mbt_authors");
	add_submenu_page("mbt_landing_page", "Genres", "Genres", 'edit_posts', "edit-tags.php?taxonomy=mbt_genres");
	add_submenu_page("mbt_landing_page", "Series", "Series", 'edit_posts', "edit-tags.php?taxonomy=mbt_series");
	add_submenu_page("mbt_landing_page", "MyBookTable Settings", "Settings", 'manage_options', "mbt_settings", 'mbt_render_settings_page');
	add_submenu_page("mbt_landing_page", "MyBookTable Help", "Help", 'edit_posts', "mbt_help", 'mbt_render_help_page');
}
add_action('admin_menu', 'mbt_add_admin_pages', 9);

function mbt_override_parent_files($parent_file) {
	global $self;
	
	if($self == "edit-tags.php" and ($_GET['taxonomy'] == "mbt_series" or $_GET['taxonomy'] == "mbt_genres" or $_GET['taxonomy'] == "mbt_authors")) {
		$parent_file = "mbt_landing_page";
	}

	return $parent_file;
}
add_filter("parent_file", 'mbt_override_parent_files'); 

function mbt_render_settings_page() {
	if(isset($_REQUEST['save_settings'])) {
		do_action("mbt_buybutton_settings_save");
		mbt_update_setting('booktable_page', $_REQUEST['mbt_booktable_page']);
		mbt_update_setting('series_in_excerpts', isset($_REQUEST['mbt_series_in_excerpts'])?true:false);
		mbt_update_setting('posts_per_page', $_REQUEST['mbt_posts_per_page']);
		mbt_update_setting('disable_seo', isset($_REQUEST['mbt_disable_seo'])?true:false);

		$settings_updated = true;
	}

	if(isset($_REQUEST['install_examples'])) {
		mbt_install_examples();
	}

	if(isset($_REQUEST['install_pages'])) {
		mbt_install_pages();
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

		<form method="post" action="">
		
			<div id="mbt-tabs">
				<ul>
					<li><a href="#tabs-1">General Settings</a></li>
					<li><a href="#tabs-2">Buy Button Settings</a></li>
					<li><a href="#tabs-3">Book Listings Settings</a></li>
					<li><a href="#tabs-4">SEO Settings</a></li>
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
										<a href="<?php echo(admin_url('edit.php?post_type=mbt_books&page=mbt_settings&install_pages=1')); ?>" id="submit" class="button button-primary">Click here to create a booktable page</a>
									<?php } ?>
									<p class="description">The Booktable page is the main landing page for your books, it must have the [mbt_booktable] shortcode.</p>
								</td>
							</tr>
							<?php if(!mbt_get_setting('installed_examples')) { ?>
								<tr valign="top">
									<th scope="row">Example Books</th>
									<td>
										<a href="<?php echo(admin_url('edit.php?post_type=mbt_books&page=mbt_settings&install_examples=1')); ?>" id="submit" class="button button-primary">Click here to create example books</a>
										<p class="description">These examples will help you learn how to set up Genres, Series, Authors, and Books of your own.</p>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes"></p>
				</div>
				<div id="tabs-2">
					<?php do_action("mbt_buybutton_settings"); ?>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes"></p>
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
								<th scope="row"><label for="mbt_posts_per_page">Number of Books per Page</label></th>
								<td>
									<input name="mbt_posts_per_page" type="text" id="mbt_posts_per_page" value="<?php echo(mbt_get_setting('posts_per_page') ? mbt_get_setting('posts_per_page') : get_option('posts_per_page')); ?>" class="regular-text">
									<p class="description">Choose the number of books to show per page on the book listings.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="blogname">Featured Buy Buttons</label></th>
								<td><p class="description">Featured Buy Buttons show up on book listings.</p></td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes"></p>
				</div>
				<div id="tabs-4">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="mbt_disable_seo">Disable SEO</label></th>
								<td>
									<input type="checkbox" name="mbt_disable_seo" id="mbt_disable_seo"  <?php echo(mbt_get_setting('disable_seo') ? ' checked="checked"' : ''); ?> >
									<p class="description">Check to disable MyBookTable's built-in SEO features.</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes"></p>
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

	<div class="wrap">
		<div id="icon-index" class="icon32"><br></div><h2>MyBookTable</h2>
		<iframe width="640" height="360" src="https://www.youtube.com/embed/gppbrYIcR80?feature=player_detailpage" frameborder="0" allowfullscreen></iframe>
		<a href="<?php echo(admin_url('admin.php?page=mbt_help')); ?>">More Tutorial Videos</a>

		<div id="welcome-panel" class="welcome-panel">
 		<input type="hidden" id="welcomepanelnonce" name="welcomepanelnonce" value="5ca8d9de51">
			<div class="welcome-panel-content">
	<h3>Welcome to MyBookTable!</h3>
	<p class="about-description">We’ve assembled some links to get you started:</p>
	<div class="welcome-panel-column-container">
	<div class="welcome-panel-column">
		<h4>Next Steps</h4>
		<ul>
					<li><a href="http://localhost:8080/wp-admin/post-new.php" class="welcome-icon welcome-write-blog">Write your first blog post</a></li>
			<li><a href="http://localhost:8080/wp-admin/post-new.php?post_type=page" class="welcome-icon welcome-add-page">Add an About page</a></li>
					<li><a href="http://localhost:8080/" class="welcome-icon welcome-view-site">View your site</a></li>
		</ul>
	</div>
	<div class="welcome-panel-column welcome-panel-last">
		<h4>More Actions</h4>
		<ul>
			<li><div class="welcome-icon welcome-widgets-menus">Manage <a href="http://localhost:8080/wp-admin/widgets.php">widgets</a> or <a href="http://localhost:8080/wp-admin/nav-menus.php">menus</a></div></li>
			<li><a href="http://localhost:8080/wp-admin/options-discussion.php" class="welcome-icon welcome-comments">Turn comments on or off</a></li>
			<li><a href="http://codex.wordpress.org/First_Steps_With_WordPress" class="welcome-icon welcome-learn-more">Learn more about getting started</a></li>
		</ul>
	</div>
	</div>
	</div>
		</div>

	</div>

<?php
}
