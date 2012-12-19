<?php

//enqueue backend plugin styles and javascript
function mbt_load_admin_style() {
	wp_register_style('mbt_admin_css', plugins_url('css/admin-style.css', dirname(__FILE__)));
	wp_enqueue_style('mbt_admin_css');
	wp_enqueue_script('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js', array('jquery'), '1.9.2');
}
add_action('admin_enqueue_scripts', 'mbt_load_admin_style');

function mbt_add_admin_pages() {
	add_submenu_page("edit.php?post_type=mbt_books", "MyBookTable Settings", "Settings", 'manage_options', "mbt_settings", 'mbt_render_settings_page');
	add_submenu_page("edit.php?post_type=mbt_books", "MyBookTable Help", "Help", 'manage_options', "mbt_help", 'mbt_render_help_page');
}
add_action('admin_menu', 'mbt_add_admin_pages');

function mbt_render_settings_page() {
	global $mbt_main_settings;

	if(isset($_REQUEST['save_settings'])) {
		$mbt_main_settings['bookstore_page'] = $_REQUEST['mbt_bookstore_page'];
		$mbt_main_settings['series_in_excerpts'] = $_REQUEST['mbt_series_in_excerpts'];
		$mbt_main_settings['posts_per_page'] = $_REQUEST['mbt_posts_per_page'];
		$mbt_main_settings['disable_seo'] = $_REQUEST['mbt_disable_seo'];
		update_option("mbt_main_settings", $mbt_main_settings);

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
					<li><a href="#tabs-2">Affiliate Settings</a></li>
					<li><a href="#tabs-3">Archive Page Settings</a></li>
					<li><a href="#tabs-4">SEO Settings</a></li>
				</ul>
				<div id="tabs-1">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="blogname">Bookstore Page</label></th>
								<td>
									<select name="mbt_bookstore_page" id="mbt_bookstore_page">
										<option value="0" <?php echo($mbt_main_settings['bookstore_page'] <= 0 ? ' selected="selected"' : '') ?> > -- Choose One -- </option>
										<?php foreach(get_pages() as $page) { ?>
											<option value="<?php echo($page->ID); ?>" <?php echo($mbt_main_settings['bookstore_page'] == $page->ID ? ' selected="selected"' : ''); ?> ><?php echo($page->post_title); ?></option>
										<?php } ?>
									</select>
									<?php if($mbt_main_settings['bookstore_page'] <= 0) { ?>
										<a href="<?php echo(admin_url('edit.php?post_type=mbt_books&page=mbt_settings&install_pages=1')); ?>" id="submit" class="button button-primary">Click here to create a bookstore page</a>
									<?php } ?>
									<p class="description">The bookstore page is the main landing page for your books, it must have the [mbt_bookstore] shortcode.</p>
								</td>
							</tr>
							<?php if(!$mbt_main_settings['installed_examples']) { ?>
								<tr valign="top">
									<th scope="row">Example Books</th>
									<td>
										<a href="<?php echo(admin_url('edit.php?post_type=mbt_books&page=mbt_settings&install_examples=1')); ?>" id="submit" class="button button-primary">Click here to create example books</a>
										<p class="description">These examples will help you learn how to set up Genres, Themes, Series, Authors, and Books of your own.</p>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes"></p>
				</div>
				<div id="tabs-2">
					<?php do_action("mbt_affiliate_settings"); ?>
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="mbt_amazon_affiliate_code">Amazon Affiliate Code</label></th>
								<td>
									<input type="text" name="mbt_amazon_affiliate_code" id="mbt_amazon_affiliate_code" value="" class="regular-text">
									<p class="description">Your personal amazon affiliate code.</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes"></p>
				</div>
				<div id="tabs-3">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="mbt_series_in_excerpts">Show other books in the same Series in excerpts</label></th>
								<td>
									<input type="checkbox" name="mbt_series_in_excerpts" id="mbt_series_in_excerpts" <?php echo($mbt_main_settings['series_in_excerpts'] ? ' checked="checked"' : ''); ?> >
									<p class="description">If checked, the related books will display in book excerpts in archive pages.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_posts_per_page">Number of Books per Page</label></th>
								<td>
									<input name="mbt_posts_per_page" type="text" id="mbt_posts_per_page" value="<?php echo($mbt_main_settings['posts_per_page'] ? $mbt_main_settings['posts_per_page'] : get_option('posts_per_page')); ?>" class="regular-text">
									<p class="description">Choose the number of books to show per page on the archive pages.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="blogname">Featured Affiliates</label></th>
								<td><p class="description">Featured affiliates show up on the Book Archive Pages.</p></td>
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
									<input type="checkbox" name="mbt_disable_seo" id="mbt_disable_seo"  <?php echo($mbt_main_settings['disable_seo'] ? ' checked="checked"' : ''); ?> >
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
		<div id="icon-options-general" class="icon32"><br></div><h2>MyBookTable Settings</h2>

		<h3>How do I kittens?</h3>
		<iframe width="640" height="360" src="https://www.youtube.com/embed/gppbrYIcR80?feature=player_detailpage" frameborder="0" allowfullscreen></iframe>
		<p>In this video we describe how to blah blah blah</p>

		<h3>How do I puppies?</h3>
		<iframe width="640" height="360" src="https://www.youtube.com/embed/5L28TM48bF0?feature=player_detailpage" frameborder="0" allowfullscreen></iframe>
		<p>In this video we describe how to blah blah blah</p>

	</div>

<?php
}
