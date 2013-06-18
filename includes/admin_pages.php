<?php

function mbt_admin_pages_init() {
	add_action('admin_menu', 'mbt_add_admin_pages', 9);
	add_action('admin_enqueue_scripts', 'mbt_enqueue_admin_styles');
	add_action('admin_enqueue_scripts', 'mbt_enqueue_admin_js');
	add_action('admin_init', 'mbt_save_settings_page');
	add_action('wp_ajax_mbt_api_key_refresh', 'mbt_api_key_refresh_ajax');
}
add_action('mbt_init', 'mbt_admin_pages_init');

function mbt_enqueue_admin_styles() {
	wp_enqueue_style('mbt-admin-css', plugins_url('css/admin-style.css', dirname(__FILE__)));
}

function mbt_enqueue_admin_js() {
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-widget');
	wp_enqueue_script('jquery-ui-position');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-tooltip', plugins_url('js/jquery.ui.tooltip.js', dirname(__FILE__)), array('jquery-ui-widget'));
	wp_enqueue_style('jquery-ui', plugins_url('css/jquery-ui.css', dirname(__FILE__)));
	wp_enqueue_script("mbt-media-upload", plugins_url('js/media-upload.js', dirname(__FILE__)));
	wp_enqueue_script("mbt-settings-page", plugins_url('js/settings-page.js', dirname(__FILE__)));
	wp_enqueue_media();
}

function mbt_add_admin_pages() {
	add_menu_page("MyBookTable", "MyBookTable", 'edit_posts', "mbt_dashboard", 'mbt_render_dashboard', plugins_url('images/icon.png', dirname(__FILE__)), '10.7');
	add_submenu_page("mbt_dashboard", "Books", "Books", 'edit_posts', "edit.php?post_type=mbt_book");
	add_submenu_page("mbt_dashboard", "Authors", "Authors", 'edit_posts', "edit-tags.php?taxonomy=mbt_author&amp;post_type=mbt_book");
	add_submenu_page("mbt_dashboard", "Genres", "Genres", 'edit_posts', "edit-tags.php?taxonomy=mbt_genre&amp;post_type=mbt_book");
	add_submenu_page("mbt_dashboard", "Series", "Series", 'edit_posts', "edit-tags.php?taxonomy=mbt_series&amp;post_type=mbt_book");
	add_submenu_page("mbt_dashboard", "MyBookTable Settings", "Settings", 'manage_options', "mbt_settings", 'mbt_render_settings_page');
	add_submenu_page("mbt_dashboard", "MyBookTable Help", "Help", 'edit_posts', "mbt_help", 'mbt_render_help_page');

	remove_menu_page("edit.php?post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit.php?post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "post-new.php?post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_author&amp;post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_genre&amp;post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_series&amp;post_type=mbt_book");
}

//needs to happen before setup.php admin_init in order to properly update admin notices
function mbt_save_settings_page() {
	if(isset($_REQUEST['page']) and $_REQUEST['page'] == 'mbt_settings' and isset($_REQUEST['save_settings'])) {
		do_action('mbt_settings_save');

		if($_REQUEST['mbt_api_key'] != mbt_get_setting('api_key')) {
			mbt_update_setting('api_key', $_REQUEST['mbt_api_key']);
			mbt_verify_api_key();
		}

		mbt_update_setting('booktable_page', $_REQUEST['mbt_booktable_page']);
		mbt_update_setting('compatibility_mode', isset($_REQUEST['mbt_compatibility_mode'])?true:false);
		mbt_update_setting('style_pack', $_REQUEST['mbt_style_pack']);
		mbt_update_setting('image_size', $_REQUEST['mbt_image_size']);

		mbt_update_setting('enable_socialmedia_badges_single_book', isset($_REQUEST['mbt_enable_socialmedia_badges_single_book'])?true:false);
		mbt_update_setting('enable_socialmedia_badges_book_excerpt', isset($_REQUEST['mbt_enable_socialmedia_badges_book_excerpt'])?true:false);
		mbt_update_setting('enable_socialmedia_bar_single_book', isset($_REQUEST['mbt_enable_socialmedia_bar_single_book'])?true:false);

		mbt_update_setting('enable_seo', isset($_REQUEST['mbt_enable_seo'])?true:false);
		mbt_update_setting('series_in_excerpts', isset($_REQUEST['mbt_series_in_excerpts'])?true:false);
		mbt_update_setting('posts_per_page', $_REQUEST['mbt_posts_per_page']);

		$settings_updated = true;
	}
}

function mbt_api_key_refresh_ajax() {
	mbt_update_setting('api_key', $_REQUEST['api_key']);
	mbt_verify_api_key();
	echo(mbt_api_key_feedback());
	die();
}

function mbt_api_key_feedback() {
	$output = '';
	if(mbt_get_setting('api_key') and mbt_get_setting('api_key_status') != 0) {
		if(mbt_get_setting('api_key_status') > 0) {
			$output .= '<span class="key_valid">Valid API Key: '.mbt_get_setting('api_key_message').'</span>';
			if(mbt_get_setting('dev_active') and !defined('MBTDEV_VERSION')) {
				$output .= '<br><a href="https://www.authormedia.com/my-account/">Download the MyBookTable Developer Add-on to activate your advanced features!</a>';
			} else if(mbt_get_setting('dev_active') and !defined('MBTDEV_VERSION')) {
				$output .= '<br><a href="https://www.authormedia.com/my-account/">Download the MyBookTable Professional Add-on to activate your advanced features!</a>';
			}
		} else {
			$output .= '<span class="key_invalid">Invalid API Key: '.mbt_get_setting('api_key_message').'</span>';
		}
	}
	return $output;
}

function mbt_render_settings_page() {
?>

	<script>
		jQuery(document).ready(function() {
			jQuery("#mbt-tabs").tabs({active: <?php echo(isset($_REQUEST['tab'])?$_REQUEST['tab']:0); ?>});
		});
	</script>

	<div class="wrap mbt_settings">
		<div id="icon-options-general" class="icon32"><br></div><h2>MyBookTable Settings</h2>
		<?php if(!empty($settings_updated)) { ?>
			<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved.</strong></p></div>
		<?php } ?>

		<form id="mbt_settings_form" method="post" action="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>">

			<div id="mbt-tabs">
				<ul>
					<li><a href="#tabs-1">General Settings</a></li>
					<li><a href="#tabs-2">Buy Button Settings</a></li>
					<li><a href="#tabs-3">Social Media Settings</a></li>
					<li><a href="#tabs-4">SEO Settings</a></li>
					<li><a href="#tabs-5">Book Listings Settings</a></li>
					<li><a href="#tabs-6">Uninstall</a></li>
				</ul>
				<div id="tabs-1">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">API Key</th>
								<td>
									<div class="mbt_api_key_feedback"><?php echo(mbt_api_key_feedback()); ?></div>
									<input type="text" name="mbt_api_key" id="mbt_api_key" value="<?php echo(mbt_get_setting('api_key')); ?>" size="60" />
									<div id="mbt_api_key_refresh"></div>
									<p class="description">If you have purchased an API Key for MyBookTable, enter it here to activate your enhanced features.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">Book Table Page</th>
								<td>
									<select name="mbt_booktable_page" id="mbt_booktable_page">
										<option value="0" <?php echo(mbt_get_setting('booktable_page') <= 0 ? ' selected="selected"' : '') ?> > -- Choose One -- </option>
										<?php foreach(get_pages() as $page) { ?>
											<option value="<?php echo($page->ID); ?>" <?php echo(mbt_get_setting('booktable_page') == $page->ID ? ' selected="selected"' : ''); ?> ><?php echo($page->post_title); ?></option>
										<?php } ?>
									</select>
									<?php if(mbt_get_setting('booktable_page') <= 0 or !get_page(mbt_get_setting('booktable_page'))) { ?>
										<a href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_install_pages=1')); ?>" id="submit" class="button button-primary">Click here to create a Book Table page</a>
									<?php } ?>
									<p class="description">The Book Table page is the main landing page for your books.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_compatibility_mode">Compatability Mode</label></th>
								<td>
									<input type="checkbox" name="mbt_compatibility_mode" id="mbt_compatibility_mode" <?php echo(mbt_get_setting('compatibility_mode') ? ' checked="checked"' : ''); ?> >
									<p class="description">Turn on theme compatability mode.</p>
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
								<th scope="row"><label for="mbt_style_pack">Style Pack</label></th>
								<td>
									<select name="mbt_style_pack" id="mbt_style_pack" style="width:100px">
										<?php $current_style = mbt_get_setting('style_pack'); ?>
										<option value="Default" <?php echo((empty($current_style) or $current_style == 'Default') ? ' selected="selected"' : '') ?> >Default</option>
										<?php foreach(mbt_get_style_packs() as $style) { ?>
											<option value="<?php echo($style); ?>" <?php echo($current_style == $style ? ' selected="selected"' : ''); ?> ><?php echo($style); ?></option>
										<?php } ?>
									</select>
									<p class="description">Choose the style pack you would like for your buy buttons.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_image_size">Book Image Size</label></th>
								<td>
									<?php $image_size = mbt_get_setting('image_size'); ?>
									<?php if(empty($image_size)) { $image_size = 'medium'; } ?>
									<?php foreach(array('small', 'medium', 'large') as $size) { ?>
										<input type="radio" name="mbt_image_size" value="<?php echo($size); ?>" <?php echo($image_size == $size ? ' checked' : ''); ?> ><?php echo(ucfirst($size)); ?><br>
									<?php } ?>
									<p class="description">Select the size of the book images.</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=0');"></p>
				</div>
				<div id="tabs-2">
					<?php do_action("mbt_buybutton_settings_render"); ?>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=1');"></p>
				</div>
				<div id="tabs-3">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="mbt_enable_socialmedia_badges_single_book">Enable Social Media Badges on Book Pages</label></th>
								<td>
									<input type="checkbox" name="mbt_enable_socialmedia_badges_single_book" id="mbt_enable_socialmedia_badges_single_book" <?php echo(mbt_get_setting('enable_socialmedia_badges_single_book') ? ' checked="checked"' : ''); ?> >
									<p class="description">Check to enable MyBookTable's social media badges on book pages.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_enable_socialmedia_badges_book_excerpt">Enable Social Media Badges on Book Listings</label></th>
								<td>
									<input type="checkbox" name="mbt_enable_socialmedia_badges_book_excerpt" id="mbt_enable_socialmedia_badges_book_excerpt" <?php echo(mbt_get_setting('enable_socialmedia_badges_book_excerpt') ? ' checked="checked"' : ''); ?> >
									<p class="description">Check to enable MyBookTable's social media badges on book listings.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_enable_socialmedia_bar_single_book">Enable Social Media Bar on Book Pages</label></th>
								<td>
									<input type="checkbox" name="mbt_enable_socialmedia_bar_single_book" id="mbt_enable_socialmedia_bar_single_book" <?php echo(mbt_get_setting('enable_socialmedia_bar_single_book') ? ' checked="checked"' : ''); ?> >
									<p class="description">Check to enable the social media bar on book pages.</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=2');"></p>
				</div>
				<div id="tabs-4">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="mbt_enable_seo">Enable SEO</label></th>
								<td>
									<input type="checkbox" name="mbt_enable_seo" id="mbt_enable_seo" <?php echo(mbt_get_setting('enable_seo') ? ' checked="checked"' : ''); ?> >
									<p class="description">Check to enable MyBookTable's built-in SEO features.</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=3');"></p>
				</div>
				<div id="tabs-5">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="mbt_series_in_excerpts">Show other books in the same Series</label></th>
								<td>
									<input type="checkbox" name="mbt_series_in_excerpts" id="mbt_series_in_excerpts" <?php echo(mbt_get_setting('series_in_excerpts') ? ' checked="checked"' : ''); ?> >
									<p class="description">If checked, the related books will display under the book in book listings.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_posts_per_page">Number of Books per Page</label></th>
								<td>
									<input name="mbt_posts_per_page" type="text" id="mbt_posts_per_page" value="<?php echo(mbt_get_setting('posts_per_page') ? mbt_get_setting('posts_per_page') : get_option('posts_per_page')); ?>" class="regular-text">
									<p class="description">Choose the number of books to show per page on the book listings.</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="Save Changes" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=4');"></p>
				</div>
				<div id="tabs-6">
					<p class="submit"><a href="<?php echo(admin_url('plugins.php?mbt_uninstall=1')); ?>" type="submit" name="save_settings" id="submit" class="button button-primary">Uninstall MyBookTable</a></p>
					<p class="description">Use this to completely uninstall all MyBookTable settings, books, series, genres, and authors. WARNING: THIS IS PERMANENT.</p>
				</div>
			</div>

		</form>

	</div>

<?php
}

function mbt_render_help_page() {
	if(!mbt_get_setting('help_page_email_subscribe_popup')) {
		mbt_update_setting('help_page_email_subscribe_popup', 'show');
		mbt_admin_email_subscribe_notice();
	}
?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div><h2>MyBookTable Help</h2>

		<h4>MyBookTable is still changing and improving all the time. We hope the following tutorials prove helpful, but they are not guaranteed to exactly line up with the current MyBookTable interface.</h4>

		<h3>Books and Series</h3>
		<iframe width="640" height="360" src="http://player.vimeo.com/video/66110874" frameborder="0" allowfullscreen></iframe>
		<p>This video shows you how to add books into a series.</p>

		<br><br><h2>More tutorial videos coming soon!</h2>

		<?php do_action("mbt_render_help_page"); ?>
	</div>

<?php
}

function mbt_render_dashboard() {
	if(!empty($_GET['subpage']) and $_GET['subpage'] == 'mbt_founders_page') { return mbt_render_founders_page(); }
?>

	<div class="wrap mbt-dashboard">
		<div id="icon-index" class="icon32"><br></div><h2>MyBookTable</h2>
		<div class="welcome-video-container">
			<div class="welcome-video welcome-panel">
				<iframe width="640" height="360" src="http://player.vimeo.com/video/66113243" frameborder="0" allowfullscreen></iframe><br>
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
							<li><a href="<?php echo(mbt_get_booktable_url()); ?>" class="welcome-icon welcome-view-site">View your Book Table</a></li>
						</ul>
					</div>
					<div class="welcome-panel-column">
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
					<div class="welcome-panel-column welcome-panel-last">
						<h4>Extra Links</h4>
						<ul>
							<li><a href="<?php echo(admin_url('admin.php?page=mbt_dashboard&subpage=mbt_founders_page')); ?>" class="welcome-icon welcome-write-blog">View Founders List</a></li>
							<li><a href="http://authormedia.us1.list-manage.com/subscribe?u=b7358f48fe541fe61acdf747b&amp;id=6b5a675fcf" class="welcome-icon welcome-write-blog" target="_blank">Get Book Marketing Tips from Author Media</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<div class="metabox-holder">
			<div id="mbt_dashboard_rss" class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle">Book Marketing Tips from Author Media</h3>
				<div class="inside">
					<?php wp_widget_rss_output(array(
						'link' => 'http://www.authormedia.com/',
						'url' => 'http://www.authormedia.com/feed/',
						'title' => 'Recent News from Author Media',
						'items' => 3,
						'show_summary' => 1,
						'show_author' => 0,
						'show_date' => 0,
					)); ?>
				</div>
			</div>
			<div id="mbt_dashboard_upsell" class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle">Current Version</h3>
				<div class="inside">
					<?php if(mbt_get_setting('dev_active')) { ?>
						<h1 class="currently-using pro">You are currently using <span class="current-version">MyBookTable Developer <?php echo(MBT_VERSION); ?></span></h1>
						<h2 class="thank-you">Thank you for your support!</h2>
					<?php } else if(mbt_get_setting('pro_active')) { ?>
						<h1 class="currently-using pro">You are currently using <span class="current-version">MyBookTable Professional <?php echo(MBT_VERSION); ?></span></h1>
						<h2 class="thank-you">Thank you for your support!</h2>
					<?php } else { ?>
						<h1 class="currently-using basic">You are currently using <span class="current-version">MyBookTable Basic <?php echo(MBT_VERSION); ?></span></h1>
						<h2 class="upgrade-title">Upgrade to MyBookTable Pro and get:</h2>
						<ul class="upgrade-list">
							<li>Free Support</li>
							<li>Amazon Affiliate Integration</li>
							<li>Barnes &amp; Noble Support</li>
							<li>Universal Buy Button</li>
							<li><a href="http://mybooktable.com" target="_blank">And much much more</a></li>
						</ul>
					<?php } ?>
				</div>
			</div>
		</div>

	</div>

<?php
}

function mbt_render_founders_page() {
?>

	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div><h2>MyBookTable Founders</h2>

		<p>This plugin was made possible by some adventurous kickstarters  We are so grateful for the members of the writing community who backed our Kickstarter project and helped us launch this plugin! Below are the ones who sponsored at the $75 level or higher. Thank you for your support!</p>
		<h3 dir="ltr">$75+ Backer Level</h3>
		<ul>
			<li><a href="http://www.stevelaube.com">Steve Laube</a>, founder of <a href="http://www.stevelaube.com">Steve Laube Agency</a></li>
			<li><a href="http://www.dianneprice-author.com/">Dianne Price</a>, author of <a href="http://www.amazon.com/dp/B00CGDPZ68">Dying Light</a></li>
			<li><a href="http://www.creatingthestory.com">Inger Fountain</a>, author of <a href="http://www.creatingthestory.com">Creating the Story</a></li>
			<li><a href="http://www.caroletowriss.com">Carole Towriss</a>, author of <a href="http://www.amazon.com/Shadow-Sinai-Journey-Canaan-ebook/dp/B00BM92PUQ/ref=sr_1_1?ie=UTF8&amp;qid=1366837954&amp;sr=8-1&amp;keywords=towriss">The Shadow of Sinai</a></li>
			<li><a href="http://www.hiswords2growby.com">Lisa Phillips</a>, author of <a href="http://www.hiswords2growby.com">Words 2 Grow By</a></li>
			<li><a href="http://tracyhigley.com">Tracy Higley</a>, author of <a href="http://amzn.to/RxemDx">So Shines the Night</a></li>
			<li><a href="http://www.normandiefischer.com">Normandie Fischer</a>, author of <a href="http://www.amazon.com/Becalmed-Normandie-Fischer/dp/1938499611/">Becalmed</a></li>
			<li><a href="http://www.judykbaer.com/">Judy Baer</a>, author of <a href="http://www.amazon.com/Judy-Baer/e/B000APHRLK/">Tales from Grace Chapel Inn</a></li>
			<li><a href="http://www.robinleehatcher.com">Robin Lee Hatcher</a>, author of <a href="http://www.amazon.com/exec/obidos/ASIN/031025809X/novelistrobinlee">Betrayed</a></li>
			<li><a href="http://www.contrarymarket.com">Krystine Kercher</a>, author of <a href="http://www.amazon.com/Shadow-Land-Legends-Astarkand/dp/148262477X/">A Shadow on the Land</a></li>
			<li><a href="http://www.mischievousmalamute.com">Harley Christensen</a>, author of <a href="http://www.amazon.com/Gemini-Rising-Mischievous-Malamute-ebook/dp/B00A9FTM3C">Gemini Rising</a></li>
			<li><a href="http://www.juliemcovert.com">Julie Covert</a>, author of <a href="http://www.amazon.com/Art-Winter-Julie-M-Covert/dp/0985369000/ref=sr_1_1?ie=UTF8&amp;qid=1366665243&amp;sr=8-1&amp;keywords=Art+of+Winter">Art of Winter</a></li>
			<li><a href="http://www.dogmathebook.com">Barbara Brunner</a>, author of <a href="http://www.amazon.com/Dog-Ma-Slobber-barbara-boswell-brunner/dp/1478106581/ref=sr_1_1?ie=UTF8&amp;qid=1366664672&amp;sr=8-1&amp;keywords=Dog-Ma">Dog-Ma: The Zen of Slobber</a></li>
			<li><a href="http://writingas.kerineal.com">Keri Neal</a>, author of <a href="http://www.amazon.com/Keri-Neal/e/B007SD2KNC">Torn</a></li>
			<li><a href="http://angelahuntbooks.com">Angela Hunt</a>, author of <a href="http://www.amazon.com/Angela-E.-Hunt/e/B000AQ1EJU/ref=sr_tc_2_0?qid=1366664404&amp;sr=8-2-ent">The Offering</a></li>
			<li><a href="http://www.calebbreakey.com">Caleb Jennings Breakey</a>, author of <a href="http://www.amazon.com/Called-Stay-Uncompromising-Mission-Church/dp/0736955429">Called to Stay</a></li>
			<li><a href="http://www.thedigitaldelusion.com">Doyle Buehler</a>, author of <a href="http://digitaldelusion.info/">The Digital Delusion</a></li>
			<li><a href="http://johnwhowell.com">John Howell</a></li>
			<li><a href="http://www.amazingthingsministry.com">Mona Corwin</a>, author of <a href="http://www.amazon.com/Table-Doing-Savoring-Scripture-together/dp/1415868417/" target="_blank">Table for Two</a></li>
			<li><a href="http://saltrunpublishing.com" target="_blank">Kellie Sharpe</a>, author of <a href="http://www.amazon.com/Surviving-Foaling-Season-ebook/dp/B00BEZBFSG" target="_blank">Surviving Foaling Season</a></li>
		</ul>
		<h3 dir="ltr">$100 + Backer Level</h3>
		<ul>
			<li><a href="http://www.thetrustdiamond.com">Tink DeWitt</a>, author of <a href="http://www.amazon.com/s/ref=nb_sb_noss?url=search-alias%3Daps&amp;field-keywords=The+Trust+Diamond">The Trust Diamond</a></li>
			<li><a href="http://authorsbroadcast.com">Reno Lovison</a>, author of <a href="http://www.amazon.com/Turn-Your-Business-Card-Into/dp/1434847683/">Turn Your Business Card into Business</a></li>
			<li><a href="http://www.alex-f-fayle.com">Alex F. Fayle</a>, author of <a href="http://www.amazon.com/An-Extraordinarily-Ordinary-Life-ebook/dp/B0051EZL54/">An Extraordinarily Ordinary Life</a></li>
			<li><a href="http://www.talkstorymedia.net">Barbara Holbrook</a>, founder of <a href="http://www.talkstorymedia.net">TalkStory Media</a></li>
			<li><a href="http://mirwriter.wordpress.com">Mir Schultz</a>, author of <a href="http://mirwriter.wordpress.com">Mir Writes</a></li>
			<li><a href="http://www.beachhousesinvabeach.com">Bruce Gwaltney</a>, author of <a href="http://www.beachhousesinvabeach.com">Beach Houses in Virginia Beach</a></li>
			<li><a href="http://www.rabbimoffic.com">Evan Moffic</a>, author of <a href="http://www.amazon.com/-/e/B00BNHHWPK">Wisdom for People of all Faiths</a></li>
			<li><a href="http://www.adminismith.com">Janica Smith</a>, author of <a href="http://www.adminismith.com">Virtual Business Solutions</a></li>
			<li><a href="http://www.booksbyjoy.com">Joy DeKok</a>, author of <a href="http://www.amazon.com/s/ref=ntt_athr_dp_sr_1?_encoding=UTF8&amp;field-author=Joy%20DeKok&amp;search-alias=books&amp;sort=relevancerank">Rain Dance</a></li>
			<li><a href="http://www.lindahoenigsberg.com">Linda Hoenigsberg</a></li>
			<li>Lisa Hendrix</li>
			<li><a href="http://vickivlucas.com/" target="_blank">Vicki Lucas</a>, author of <a href="http://www.amazon.com/Vicki-V.-Lucas/e/B006X7117U/ref=ntt_athr_dp_pel_1" target="_blank">Toxic</a></li>
			<li><a href="http://www.Hunting-America.com" target="_blank">Richard James</a></li>
		</ul>
		<h3>$150 + Backer Level</h3>
		<ul>
			<li><a href="http://www.dollarplanning.com">Brenda Taylor</a>, author of <a href="http://www.dollarplanning.com">Dollar Planning</a></li>
			<li><a href="http://lauradomino.com">Laura Domino</a>, author of <a href="http://lauradomino.com">Laura Domino</a></li>
			<li><a href="http://www.warmenhoven.co">Adrianus Warmenhoven</a>, author of <a href="http://www.warmenhoven.co">Warmenhoven</a></li>
			<li><a href="http://www.accidentalauthor.ca">Mike Hartner</a>, author of <a href="http://www.amazon.com/I-Walter-ebook/dp/B00C7FJ7B4/ref=sr_1_1?ie=UTF8&amp;qid=1366292416&amp;sr=8-1&amp;keywords=%22I%2C+Walter%22">I, Walter</a></li>
			<li><a href="http://www.kathleenoverby.com">Kathleen Overby</a></li>
			<li><a href="http://vivianmabuni.com/">Vivian Mabuni</a>, author of <a href="http://vivianmabuni.com/">Warrior in Pink</a></li>
			<li><a href="http://wadewebster.com">Wade Webster</a></li>
			<li><a href="http://gloriaclover.com">Gloria Clover</a>, author of <a href="http://www.amazon.com/Children-King-Book-Two-ebook/dp/B008W1AUUO/ref=sr_1_1?ie=UTF8&amp;qid=1366664833&amp;sr=8-1&amp;keywords=The+Fire+Starter%2C+Clover">The Fire Starter</a></li>
			<li><a href="http://www.lisabergren.com">Lisa Bergren</a>, author of <a href="http://www.amazon.com/Glamorous-Illusions-Novel-Grand-Series/dp/1434764303/ref=tmm_pap_title_0">Glamorous Illusions</a></li>
			<li><a href="http://techguyjay.com/books" target="_blank">Josh Goldsmith</a></li>
			<li><a href="http://www.DebiJHolliday.com" target="_blank">Debi J. Holliday</a></li>
			<li><a href="http://www.nickbuchan.com" target="_blank">Nick and Lu</a></li>
			<li><a href="http://www.sbbflonghorns.com" target="_blank">Chrisann Merriman</a></li>
			<li><a href="http://www.cloudlinkco.com" target="_blank">Brandon Frye</a></li>
			<li>Diane Finlayson</li>
			<li>David Buggs</li>
		</ul>
		<h3 dir="ltr">$250+ Backer Level</h3>
		<ul>
			<li><a href="http://christopherschmitt.com/">Christopher Schmitt</a>, author of <a href="http://www.amazon.com/Designing-Web-Mobile-Graphics-Fundamental/dp/0321858549/">Designing Web and Mobile Graphics</a></li>
			<li><a href="http://hotappleciderbooks.com">Les and N.J. Lindquist</a>, authors of <a href="http://www.amazon.com/Second-Cup-Hot-Apple-Cider/dp/0978496310/ref=sr_1_3?s=books&amp;ie=UTF8&amp;qid=1366743096&amp;sr=1-3">A Second Cup of Apple Cider</a></li>
			<li><a href="http://www.inboundmastery.com">Tony Tovar</a>, <a href="http://www.amazon.com/dp/B008R1F446">How to Make Money from Writing Online</a></li>
			<li><a href="http://www.remcdermott.com">R.E. McDermott</a>, author of <a href="http://www.amazon.com/Deadly-Straits-Dugan-Novel-ebook/dp/B0057AMO2A">Deadly Straits</a></li>
			<li><a href="http://www.marydemuth.com">Mary DeMuth</a>, author of <a href="http://amzn.to/sDBhqT">The 11 Secrets of Getting Published</a></li>
			<li><a href="http://livinignited.org/Livin_Ignited/Home.html">Nancy Grisham</a>, author of <a href="http://www.amazon.com/Thriving-Trusting-God-Life-Fullest/dp/080101543X/ref=sr_1_1?ie=UTF8&amp;qid=1366995488&amp;sr=8-1&amp;keywords=nancy+grisham">Thriving: Trusting God for Life to the Fullest</a></li>
			<li><a href="http://www.markmittleburg.com">Mark Mittleberg</a>, author of <a href="http://www.amazon.com/Confident-Faith-Building-Foundation-Beliefs/dp/1414329962/ref=sr_1_2?s=books&amp;ie=UTF8&amp;qid=1367010724&amp;sr=1-2&amp;keywords=confident+faith">Confident Faith</a></li>
			<li><a href="http://www.ageviewpress.com">Jeanette Vaughan</a>, author of <a href="http://www.amazon.com/Flying-Solo-Unconventional-Navigates-Turbulence/dp/061561888X/ref=sr_1_1?ie=UTF8&amp;qid=1366856431&amp;sr=8-1&amp;keywords=jeanette+vaughan+flying+solo">Flying Solo</a></li>
			<li><a href="http://markmccluretoday.com" target="_blank">Mark McClure</a></li>
			<li><a href="http://www.recalculatingthebook.com/" target="_blank">Dennis Pappenfus</a></li>
			<li><a href="http://www.advancedfictionwriting.com">Randy Ingermanson</a>, author of <a href="http://www.amazon.com/Writing-Fiction-Dummies-Randy-Ingermanson/dp/0470530707/">Writing Fiction for Dummies</a></li>
			<li><a href="http://www.qualityusproducts.com">Ellen Pope</a></li>
		</ul>

	</div>

<?php
}
