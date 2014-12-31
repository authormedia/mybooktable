<?php

function mbt_admin_pages_init() {
	if(is_admin()) {
		add_action('admin_menu', 'mbt_add_admin_pages', 9);
		add_action('admin_enqueue_scripts', 'mbt_enqueue_admin_styles');
		add_action('admin_enqueue_scripts', 'mbt_enqueue_admin_js');
		add_action('admin_init', 'mbt_save_settings_page');
		add_action('wp_ajax_mbt_api_key_refresh', 'mbt_api_key_refresh_ajax');
	}
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
	wp_enqueue_style('mbt-jquery-ui', plugins_url('css/jquery-ui.css', dirname(__FILE__)));
	wp_enqueue_script("mbt-media-upload", plugins_url('js/media-upload.js', dirname(__FILE__)));
	wp_enqueue_script("mbt-settings-page", plugins_url('js/settings-page.js', dirname(__FILE__)));
	if(function_exists('wp_enqueue_media')) { wp_enqueue_media(); }
	wp_localize_script('mbt-media-upload', 'mbt_media_upload_i18n', array(
		'mbt_upload_sample_button' => __('Sample Chapter Image', 'mybooktable'),
		'mbt_upload_tax_image_button' => __('Taxonomy Image', 'mybooktable'),
		'mbt_set_book_image_button' => __('Book Cover Image', 'mybooktable'),
		'select' => __('Select', 'mybooktable')
	));
}

function mbt_add_admin_pages() {
	add_menu_page(__("MyBookTable"), __("MyBookTable", 'mybooktable'), 'edit_posts', "mbt_dashboard", 'mbt_render_dashboard', 'dashicons-book', '10.7');
	add_submenu_page("mbt_dashboard", __("Books", 'mybooktable'), __("Books", 'mybooktable'), 'edit_posts', "edit.php?post_type=mbt_book");
	add_submenu_page("mbt_dashboard", __("Add Book", 'mybooktable'), __("Add Book", 'mybooktable'), 'edit_posts', "post-new.php?post_type=mbt_book");
	add_submenu_page("mbt_dashboard", __("Authors", 'mybooktable'), __("Authors", 'mybooktable'), 'edit_posts', "edit-tags.php?taxonomy=mbt_author&amp;post_type=mbt_book");
	add_submenu_page("mbt_dashboard", __("Genres", 'mybooktable'), __("Genres", 'mybooktable'), 'edit_posts', "edit-tags.php?taxonomy=mbt_genre&amp;post_type=mbt_book");
	add_submenu_page("mbt_dashboard", __("Series", 'mybooktable'), __("Series", 'mybooktable'), 'edit_posts', "edit-tags.php?taxonomy=mbt_series&amp;post_type=mbt_book");
	add_submenu_page("mbt_dashboard", __("Tags", 'mybooktable'), __("Tags", 'mybooktable'), 'edit_posts', "edit-tags.php?taxonomy=mbt_tag&amp;post_type=mbt_book");
	add_submenu_page("mbt_dashboard", __("MyBookTable Settings", 'mybooktable'), __("Settings", 'mybooktable'), 'manage_options', "mbt_settings", 'mbt_render_settings_page');
	add_submenu_page("mbt_dashboard", __("MyBookTable Help", 'mybooktable'), __("Help", 'mybooktable'), 'edit_posts', "mbt_help", 'mbt_render_help_page');

	remove_menu_page("edit.php?post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit.php?post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "post-new.php?post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_author&amp;post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_genre&amp;post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_series&amp;post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_tag&amp;post_type=mbt_book");
}

function mbt_hide_api_key($key) {
	return substr($key, 0, 4) . str_repeat("*", max(0, strlen($key)-4));
}

//needs to happen before setup.php admin_init in order to properly update admin notices
function mbt_save_settings_page() {
	if(isset($_REQUEST['page']) and $_REQUEST['page'] == 'mbt_settings' and isset($_REQUEST['save_settings'])) {
		do_action('mbt_settings_save');

		if($_REQUEST['mbt_api_key'] != mbt_get_setting('api_key') and $_REQUEST['mbt_api_key'] != mbt_hide_api_key(mbt_get_setting('api_key'))) {
			mbt_update_setting('api_key', $_REQUEST['mbt_api_key']);
			mbt_verify_api_key();
		}
		mbt_update_setting('product_name', $_REQUEST['mbt_product_name']);
		mbt_update_setting('product_slug', sanitize_title($_REQUEST['mbt_product_name']));

		mbt_update_setting('booktable_page', $_REQUEST['mbt_booktable_page']);
		mbt_update_setting('compatibility_mode', isset($_REQUEST['mbt_compatibility_mode']));

		mbt_update_setting('enable_socialmedia_badges_single_book', isset($_REQUEST['mbt_enable_socialmedia_badges_single_book']));
		mbt_update_setting('enable_socialmedia_badges_book_excerpt', isset($_REQUEST['mbt_enable_socialmedia_badges_book_excerpt']));
		mbt_update_setting('enable_socialmedia_bar_single_book', isset($_REQUEST['mbt_enable_socialmedia_bar_single_book']));

		mbt_update_setting('enable_seo', isset($_REQUEST['mbt_enable_seo']));

		mbt_update_setting('style_pack', $_REQUEST['mbt_style_pack']);
		mbt_update_setting('image_size', $_REQUEST['mbt_image_size']);
		mbt_update_setting('enable_breadcrumbs', isset($_REQUEST['mbt_enable_breadcrumbs']));
		mbt_update_setting('show_series', isset($_REQUEST['mbt_show_series']));
		mbt_update_setting('series_in_excerpts', isset($_REQUEST['mbt_series_in_excerpts']));
		mbt_update_setting('show_find_bookstore', isset($_REQUEST['mbt_show_find_bookstore']));
		mbt_update_setting('hide_domc_notice', !isset($_REQUEST['mbt_hide_domc_notice']));
		mbt_update_setting('posts_per_page', $_REQUEST['mbt_posts_per_page']);
		mbt_update_setting('book_button_size', $_REQUEST['mbt_book_button_size']);
		mbt_update_setting('listing_button_size', $_REQUEST['mbt_listing_button_size']);
		mbt_update_setting('widget_button_size', $_REQUEST['mbt_widget_button_size']);

		$settings_updated = true;
	} else if(isset($_REQUEST['page']) and $_REQUEST['page'] == 'mbt_settings' and isset($_REQUEST['save_default_affiliate_settings'])) {
		if(isset($_REQUEST['mbt_enable_default_affiliates'])) { mbt_update_setting('enable_default_affiliates', true); }
		if(isset($_REQUEST['mbt_disable_default_affiliates'])) { mbt_update_setting('enable_default_affiliates', false); }
	}

	if(isset($_REQUEST['mbt_remove_booktable_page'])) { mbt_update_setting('booktable_page', 0); }
}

function mbt_api_key_refresh_ajax() {
	if($_REQUEST['api_key'] != mbt_hide_api_key(mbt_get_setting('api_key'))) {
		mbt_update_setting('api_key', $_REQUEST['api_key']);
		mbt_verify_api_key();
	}
	echo(mbt_api_key_feedback());
	die();
}

function mbt_api_key_feedback() {
	$output = '';
	if(mbt_get_setting('api_key') and mbt_get_setting('api_key_status') != 0) {
		if(mbt_get_setting('api_key_status') > 0) {
			$output .= '<span class="key_valid">'.__('Valid API Key', 'mybooktable').': '.mbt_get_setting('api_key_message').'</span>';
			if(mbt_get_setting('dev_active') and !defined('MBTDEV_VERSION')) {
				$output .= '<br><a href="https://gumroad.com/library/">'.__('Download the MyBookTable Developer Add-on to activate your advanced features!', 'mybooktable').'</a>';
			} else if(mbt_get_setting('pro_active') and !mbt_get_setting('dev_active') and !defined('MBTPRO_VERSION')) {
				$output .= '<br><a href="https://gumroad.com/library/">'.__('Download the MyBookTable Professional Add-on to activate your advanced features!', 'mybooktable').'</a>';
			}
		} else {
			$output .= '<span class="key_invalid">'.__('Invalid API Key', 'mybooktable').': '.mbt_get_setting('api_key_message').'</span>';
		}
	}
	return $output;
}

function mbt_render_settings_page() {
	if(!empty($_GET['mbt_setup_default_affiliates'])) { return mbt_render_setup_default_affiliates_page(); }
	if(!empty($_GET['mbt_getnoticed_books_import'])) { return mbt_render_getnoticed_books_import_page(); }
?>

	<script>
		jQuery(document).ready(function() {
			jQuery("#mbt-tabs").tabs({active: <?php echo(isset($_REQUEST['tab'])?$_REQUEST['tab']:0); ?>});
		});
	</script>

	<div class="wrap mbt_settings">
		<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('MyBookTable Settings', 'mybooktable'); ?></h2>
		<?php if(!empty($settings_updated)) { ?>
			<div id="setting-error-settings_updated" class="updated settings-error"><p><strong><?php _e('Settings saved', 'mybooktable'); ?>.</strong></p></div>
		<?php } ?>

		<form id="mbt_settings_form" method="post" action="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>">

			<div id="mbt-tabs">
				<ul>
					<li><a href="#tabs-1"><?php _e('General Settings', 'mybooktable'); ?></a></li>
					<li><a href="#tabs-2"><?php _e('Affiliate Settings', 'mybooktable'); ?></a></li>
					<li><a href="#tabs-3"><?php _e('Social Media Settings', 'mybooktable'); ?></a></li>
					<li><a href="#tabs-4"><?php _e('SEO Settings', 'mybooktable'); ?></a></li>
					<li><a href="#tabs-5"><?php _e('Display Settings', 'mybooktable'); ?></a></li>
					<li><a href="#tabs-6"><?php _e('Uninstall', 'mybooktable'); ?></a></li>
				</ul>
				<div id="tabs-1">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><?php _e('MyBookTable API Key', 'mybooktable'); ?></th>
								<td>
									<div class="mbt_api_key_feedback"><?php echo(mbt_api_key_feedback()); ?></div>
									<input type="text" name="mbt_api_key" id="mbt_api_key" value="<?php echo(mbt_hide_api_key(mbt_get_setting('api_key'))); ?>" size="60" class="regular-text" />
									<div id="mbt_api_key_refresh"></div>
									<p class="description"><?php _e('If you have purchased an Add-On API Key for MyBookTable, enter it here to activate your enhanced features. You can find it in your <a href="https://gumroad.com/library/" target="_blank">Gumroad Library here</a>. If you would like to purchase an Add-On API key visit <a href="http://www.authormedia.com/mybooktable/">AuthorMedia.com/MyBookTable</a>.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e('Book Table Page', 'mybooktable'); ?></th>
								<td>
									<select name="mbt_booktable_page" id="mbt_booktable_page">
										<option value="0" <?php echo(mbt_get_setting('booktable_page') <= 0 ? ' selected="selected"' : '') ?> ><?php _e('-- Choose One --', 'mybooktable');?></option>
										<?php foreach(get_pages() as $page) { ?>
											<option value="<?php echo($page->ID); ?>" <?php echo(mbt_get_setting('booktable_page') == $page->ID ? ' selected="selected"' : ''); ?> ><?php echo($page->post_title); ?></option>
										<?php } ?>
									</select>
									<?php if(mbt_get_setting('booktable_page') <= 0 or !get_page(mbt_get_setting('booktable_page'))) { ?>
										<a href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_install_pages=1')); ?>" id="submit" class="button button-primary"><?php _e('Click here to create a Book Table page', 'mybooktable'); ?></a>
									<?php } else { ?>
										<a href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_remove_booktable_page=1')); ?>" id="submit" class="button button-primary"><?php _e('Remove Book Table page', 'mybooktable'); ?></a>
									<?php } ?>
									<p class="description"><?php _e('The Book Table page is the main landing page for your books.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_compatibility_mode"><?php _e('Compatability Mode', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_compatibility_mode" id="mbt_compatibility_mode" <?php echo(mbt_get_setting('compatibility_mode') ? ' checked="checked"' : ''); ?> >
									<p class="description"><?php _e('Checked = More Compatible Out of the Box. Unchecked = More Developer Control.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<?php if(!mbt_get_setting('installed_examples')) { ?>
								<tr valign="top">
									<th scope="row"><?php _e('Example Books', 'mybooktable'); ?></th>
									<td>
										<a href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_install_examples=1')); ?>" id="submit" class="button button-primary"><?php _e('Click here to create example books', 'mybooktable'); ?></a>
										<p class="description"><?php _e('These examples will help you learn how to set up Genres, Series, Authors, and Books of your own.', 'mybooktable'); ?></p>
									</td>
								</tr>
							<?php } ?>
							<tr valign="top">
								<th scope="row"><?php _e('MyBookTable Product Name', 'mybooktable'); ?></th>
								<td>
									<input type="text" name="mbt_product_name" id="mbt_product_name" value="<?php echo(mbt_get_setting('product_name')); ?>" size="60" class="regular-text" />
									<p class="description"><?php _e('You can use this to change the "books" slug used in the book page urls if you are selling something other than books, such as "DVDs", "Movies", or simply "Products".', 'mybooktable'); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					<?php do_action("mbt_general_settings_render"); ?>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'mybooktable'); ?>" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=0');"></p>
				</div>
				<div id="tabs-2">
					<?php do_action("mbt_affiliate_settings_render"); ?>
					<?php
						if(!mbt_get_setting('pro_active') and !mbt_get_setting('dev_active')) {
							echo('<hr>');
							if(mbt_get_setting('enable_default_affiliates')) {
								_e('Amazon and Barnes &amp; Noble Buy Buttons enabled! <a href="admin.php?page=mbt_settings&mbt_setup_default_affiliates=1">Disable</a>', 'mybooktable');
							} else {
								_e('Amazon and Barnes &amp; Noble Buy Buttons disabled! <a href="admin.php?page=mbt_settings&mbt_setup_default_affiliates=1">Enable</a>', 'mybooktable');
							}
							echo('&nbsp;&nbsp;<a href="admin.php?page=mbt_settings&mbt_setup_default_affiliates=1" style="font-size:10px">'.__('What does this mean?', 'mybooktable').'</a>');
						}
					?>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'mybooktable'); ?>" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=1');"></p>
				</div>
				<div id="tabs-3">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="mbt_enable_socialmedia_badges_single_book"><?php _e('Enable Social Media Badges on Book Pages', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_enable_socialmedia_badges_single_book" id="mbt_enable_socialmedia_badges_single_book" <?php echo(mbt_get_setting('enable_socialmedia_badges_single_book') ? ' checked="checked"' : ''); ?> >
									<p class="description"><?php _e('Check to enable MyBookTable\'s social media badges on book pages.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_enable_socialmedia_badges_book_excerpt"><?php _e('Enable Social Media Badges on Book Listings', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_enable_socialmedia_badges_book_excerpt" id="mbt_enable_socialmedia_badges_book_excerpt" <?php echo(mbt_get_setting('enable_socialmedia_badges_book_excerpt') ? ' checked="checked"' : ''); ?> >
									<p class="description"><?php _e('Check to enable MyBookTable\'s social media badges on book listings.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_enable_socialmedia_bar_single_book"><?php _e('Enable Social Media Bar on Book Pages', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_enable_socialmedia_bar_single_book" id="mbt_enable_socialmedia_bar_single_book" <?php echo(mbt_get_setting('enable_socialmedia_bar_single_book') ? ' checked="checked"' : ''); ?> >
									<p class="description"><?php _e('Check to enable the social media bar on book pages.', 'mybooktable'); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'mybooktable'); ?>" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=2');"></p>
				</div>
				<div id="tabs-4">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="mbt_enable_seo"><?php _e('Enable SEO', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_enable_seo" id="mbt_enable_seo" <?php echo(mbt_get_setting('enable_seo') ? ' checked="checked"' : ''); ?> >
									<p class="description"><?php _e('Check to enable MyBookTable\'s built-in SEO features.', 'mybooktable'); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'mybooktable'); ?>" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=3');"></p>
				</div>
				<div id="tabs-5">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="mbt_style_pack"><?php _e('Style Pack', 'mybooktable'); ?></label></th>
								<td>
									<select name="mbt_style_pack" id="mbt_style_pack" style="width:100px">
										<?php $current_style = mbt_get_setting('style_pack'); ?>
										<option value="Default" <?php echo((empty($current_style) or $current_style == 'Default') ? ' selected="selected"' : '') ?> ><?php _e('Default', 'mybooktable'); ?></option>
										<?php foreach(mbt_get_style_packs() as $style) { ?>
											<option value="<?php echo($style); ?>" <?php echo($current_style == $style ? ' selected="selected"' : ''); ?> ><?php echo($style); ?></option>
										<?php } ?>
									</select>
									<p class="description"><?php _e('Choose the style pack you would like for your buy buttons.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_image_size"><?php _e('Book Image Size', 'mybooktable'); ?></label></th>
								<td>
									<?php $sizes = array('small' =>__('Small', 'mybooktable'), 'medium' => __('Medium', 'mybooktable'), 'large' => __('Large', 'mybooktable')); ?>
									<?php $image_size = mbt_get_setting('image_size'); ?>
									<?php if(empty($image_size)) { $image_size = 'medium'; } ?>
									<?php foreach($sizes as $size => $size_name) { ?>
										<input type="radio" name="mbt_image_size" value="<?php echo($size); ?>" <?php echo($image_size == $size ? ' checked' : ''); ?> ><?php echo($size_name); ?><br>
									<?php } ?>
									<p class="description"><?php _e('Select the size of the book images.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_enable_seo"><?php _e('Enable Breadcrumbs', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_enable_breadcrumbs" id="mbt_enable_breadcrumbs" <?php echo(mbt_get_setting('enable_breadcrumbs') ? ' checked="checked"' : ''); ?> >
									<p class="description"><?php _e('Check to enable MyBookTable\'s built-in breadcrumbs.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_show_series"><?php _e('Show other books in the same series', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_show_series" id="mbt_show_series" <?php echo(mbt_get_setting('show_series') ? ' checked="checked"' : ''); ?> >
									<p class="description"><?php _e('If checked, the other books in the same series will display under the book.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_series_in_excerpts"><?php _e('Show other books in the same series on book listings', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_series_in_excerpts" id="mbt_series_in_excerpts" <?php echo(mbt_get_setting('series_in_excerpts') ? ' checked="checked"' : ''); ?> >
									<p class="description"><?php _e('If checked, the other books in the same series will display under the books on book listings.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_show_series"><?php _e('Show the "Find A Local Bookstore" box', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_show_find_bookstore" id="mbt_show_find_bookstore" <?php echo(mbt_get_setting('show_find_bookstore') ? ' checked="checked"' : ''); ?> >
									<p class="description"><?php _e('If checked, a box that helps your readers find places to buy your book will display under the book.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_posts_per_page"><?php _e('Number of Books per Page on Book Listings', 'mybooktable'); ?></label></th>
								<td>
									<input name="mbt_posts_per_page" type="text" id="mbt_posts_per_page" value="<?php echo(mbt_get_setting('posts_per_page') ? mbt_get_setting('posts_per_page') : get_option('posts_per_page')); ?>" class="regular-text">
									<p class="description"><?php _e('Choose the number of books to show per page on the book listings', 'mybooktable'); ?>.</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_hide_domc_notice"><?php _e('Show Disclosure of Material Connection Disclaimer', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_hide_domc_notice" id="mbt_hide_domc_notice" <?php echo(!mbt_get_setting('hide_domc_notice') ? ' checked="checked"' : ''); ?> >
									<p class="description"><?php _e('Displays a Disclosure of Material Connection Disclaimer below the single book page content.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_image_size"><?php _e('Buy Button Size on Book Pages', 'mybooktable'); ?></label></th>
								<td>
									<?php $book_button_size = mbt_get_setting('book_button_size'); ?>
									<?php if(empty($book_button_size)) { $book_button_size = 'medium'; } ?>
									<?php foreach($sizes as $size => $size_name) { ?>
										<input type="radio" name="mbt_book_button_size" value="<?php echo($size); ?>" <?php echo($book_button_size == $size ? ' checked' : ''); ?> ><?php echo($size_name); ?><br>
									<?php } ?>
									<p class="description"><?php _e('Select the size of the buy buttons on book pages.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_image_size"><?php _e('Buy Button Size on Book Listings', 'mybooktable'); ?></label></th>
								<td>
									<?php $listing_button_size = mbt_get_setting('listing_button_size'); ?>
									<?php if(empty($listing_button_size)) { $listing_button_size = 'medium'; } ?>
									<?php foreach($sizes as $size => $size_name) { ?>
										<input type="radio" name="mbt_listing_button_size" value="<?php echo($size); ?>" <?php echo($listing_button_size == $size ? ' checked' : ''); ?> ><?php echo($size_name); ?><br>
									<?php } ?>
									<p class="description"><?php _e('Select the size of the buy buttons on book listings.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="mbt_image_size"><?php _e('Buy Button Size on Widgets', 'mybooktable'); ?></label></th>
								<td>
									<?php $widget_button_size = mbt_get_setting('widget_button_size'); ?>
									<?php if(empty($widget_button_size)) { $widget_button_size = 'medium'; } ?>
									<?php foreach($sizes as $size => $size_name) { ?>
										<input type="radio" name="mbt_widget_button_size" value="<?php echo($size); ?>" <?php echo($widget_button_size == $size ? ' checked' : ''); ?> ><?php echo($size_name); ?><br>
									<?php } ?>
									<p class="description"><?php _e('Select the size of the buy buttons on widgets.', 'mybooktable'); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_settings" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'mybooktable'); ?>" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=4');"></p>
				</div>
				<div id="tabs-6">
					<p class="submit"><a href="<?php echo(admin_url('plugins.php?mbt_uninstall=1')); ?>" type="submit" name="save_settings" id="submit" class="button button-primary"><?php _e('Uninstall MyBookTable', 'mybooktable'); ?></a></p>
					<p class="description"><?php _e('Use this to completely uninstall all MyBookTable settings, books, series, genres, tags, and authors. WARNING: THIS IS PERMANENT.', 'mybooktable'); ?></p>
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
		<div id="icon-options-general" class="icon32"><br></div><h2 style="font-weight:bold"><?php _e('MyBookTable Help', 'mybooktable'); ?></h2>

		<br><a href="https://gumroad.com/library/" target="_blank">Need to find or manage your API key? Access through Gumroad</a>

		<?php if(!mbt_get_setting('pro_active') and !mbt_get_setting('dev_active')) { ?>
			<br><a href="http://www.authormedia.com/mybooktable/add-ons/" target="_blank"><?php _e('Need Premium Support? Purchase an add-on here.', 'mybooktable'); ?></a><br><br>
		<?php } else { ?>
			<h3><?php _e('Premium Support Options', 'mybooktable'); ?></h3>
			<a href="http://authormedia.freshdesk.com/support/tickets/new" target="_blank"><?php _e('Submit a Ticket', 'mybooktable'); ?></a><br>
			<a href="http://authormedia.freshdesk.com/support/discussions" target="_blank"><?php _e('Visit the Support Forum', 'mybooktable'); ?></a><br>
			<a href="http://authormedia.freshdesk.com/support/discussions/topics/new" target="_blank"><?php _e('Suggest a Feature', 'mybooktable'); ?></a><br>
			<a href="http://authormedia.freshdesk.com/support/tickets/new" target="_blank"><?php _e('Submit a Bug', 'mybooktable'); ?></a><br><br>
		<?php } ?>

		<h2><?php _e('MyBookTable Tutorials', 'mybooktable'); ?></h2>
		<ul>
			<li><a href="http://www.authormedia.com/how-to-add-goodreads-book-reviews-to-mybooktable/"><?php _e('How to Add GoodReads Book Reviews to MyBookTable', 'mybooktable'); ?></a></li>
		</ul>

		<h2><?php _e('General WordPress Guides &amp; Tutorials', 'mybooktable'); ?></h2>
		<ul>
			<li><a href="http://www.authormedia.com/10-elements-proven-to-draw-readers-to-your-novels-website/" rel="bookmark"><?php _e('10 Ways Proven to Draw Readers to Your Novel\'s Website', 'mybooktable'); ?></a></li>
			<li><a href="http://www.authormedia.com/how-to-upload-a-file-to-your-wordpress-site/" rel="bookmark"><?php _e('How to Upload a File to Your WordPress Site', 'mybooktable'); ?></a></li>
			<li><a href="http://www.authormedia.com/how-to-create-a-pdf/" rel="bookmark"><?php _e('How to Create a PDF', 'mybooktable'); ?></a></li>
			<li><a href="http://www.authormedia.com/how-to-add-a-hyperlink-to-wordpress/" rel="bookmark"><?php _e('How to Add a Hyperlink to WordPress', 'mybooktable'); ?></a></li>
			<li><a href="http://www.authormedia.com/the-wordpress-hotkey-cheat-sheet-every-author-needs/" rel="bookmark"><?php _e('The WordPress Hotkey Cheat Sheet Every Author Needs', 'mybooktable'); ?></a></li>
			<li><a href="http://www.authormedia.com/how-to-create-or-edit-posts-in-wordpress/" rel="bookmark"><?php _e('How to Create or Edit Posts in WordPress', 'mybooktable'); ?></a></li>
			<li><a href="http://www.authormedia.com/how-to-keep-your-wordpress-site-secure-from-hackers/" rel="bookmark"><?php _e('How to Keep Your WordPress Site Secure From Hackers', 'mybooktable'); ?></a></li>
			<li><a href="http://www.authormedia.com/how-to-find-zen-mode-in-wordpress/" rel="bookmark"><?php _e('How To Find Zen Mode in WordPress', 'mybooktable'); ?></a></li>
		</ul>

		<h2><?php _e('MyBookTable Tutorial Videos', 'mybooktable'); ?></h2>
		<h3><?php _e('MyBookTable Overview', 'mybooktable'); ?></h3>
		<iframe width="640" height="360" src="http://player.vimeo.com/video/66113243" frameborder="0" allowfullscreen></iframe>
		<p><?php _e('This video is a general introduction to MyBookTable.', 'mybooktable'); ?></p>

		<h3><?php _e('How to Add Buy Buttons', 'mybooktable'); ?></h3>
		<iframe width="640" height="360" src="http://player.vimeo.com/video/68790296" frameborder="0" allowfullscreen></iframe>
		<p><?php _e('This video shows you how to add buy buttons to your books.', 'mybooktable'); ?></p>

		<h3><?php _e('How to Put Books in a Series', 'mybooktable'); ?></h3>
		<iframe width="640" height="360" src="http://player.vimeo.com/video/66110874" frameborder="0" allowfullscreen></iframe>
		<p><?php _e('This video shows you how to add books into a series.', 'mybooktable'); ?></p>

		<h3><?php _e('How to Setup an Amazon Affiliate Account With MyBookTable', 'mybooktable'); ?></h3>
		<iframe width="640" height="360" src="http://player.vimeo.com/video/69188658" frameborder="0" allowfullscreen></iframe>
		<p><?php _e('This video walks you through setting up an Amazon Affiliate account and how to take your affiliate code and insert it into your MyBookTable plugin.', 'mybooktable'); ?></p>

		<h3><?php _e('Effective Book Blurb Strategies', 'mybooktable'); ?></h3>
		<iframe width="640" height="360" src="http://www.youtube.com/embed/LABESfhThhY" frameborder="0" allowfullscreen></iframe>
		<p><?php _e('This video shows you how to write book blurbs for your books.', 'mybooktable'); ?></p>

		<h2><?php _e('More tutorial videos coming soon!', 'mybooktable'); ?></h2>

		<?php do_action("mbt_render_help_page", 'mybooktable'); ?>

		<br>
		<h2>Additional Support</h2>
		<p>You can also check out WordPress' <a href="http://wordpress.org/support/plugin/mybooktable">MyBookTable Support Forum</a>.</p>
	</div>

<?php
}

add_filter('wp101_get_custom_help_topics', 'mbt_add_wp101_help');
function mbt_add_wp101_help($videos) {
	$videos["mbt-overview"] = array("title" => "MyBookTable Overview", "content" => '<iframe width="640" height="360" src="http://player.vimeo.com/video/66113243" frameborder="0" allowfullscreen></iframe>');
	$videos["mbt-buybuttons"] = array("title" => "MyBookTable Buy Buttons", "content" => '<iframe width="640" height="360" src="http://player.vimeo.com/video/68790296" frameborder="0" allowfullscreen></iframe>');
	$videos["mbt-amazonaffiliates"] = array("title" => "MyBookTable Amazon Affiliate Accounts", "content" => '<iframe width="640" height="360" src="http://player.vimeo.com/video/69188658" frameborder="0" allowfullscreen></iframe>');
	$videos["mbt-bookblurbs"] = array("title" => "MyBookTable Book Blurbs", "content" => '<iframe width="640" height="360" src="http://www.youtube.com/embed/LABESfhThhY" frameborder="0" allowfullscreen></iframe>');
	return $videos;
}

function mbt_render_dashboard() {
	if(!empty($_GET['subpage']) and $_GET['subpage'] == 'mbt_founders_page') { return mbt_render_founders_page(); }
	if(!empty($_GET['subpage']) and $_GET['subpage'] == 'mbt_download_addons_page') { return mbt_render_download_addons_page(); }
?>

	<div class="wrap mbt-dashboard">
		<div id="icon-index" class="icon32"><br></div><h2><?php _e('MyBookTable', 'mybooktable'); ?></h2>
		<div class="welcome-video-container">
			<div class="welcome-video welcome-panel">
				<iframe width="640" height="360" src="http://player.vimeo.com/video/66113243" frameborder="0" allowfullscreen></iframe><br>
				<a href="<?php echo(admin_url('admin.php?page=mbt_help')); ?>"><?php _e('More Tutorial Videos', 'mybooktable'); ?></a>
			</div>
		</div>

		<div class="buttons-container">
			<a href="<?php echo(admin_url('post-new.php?post_type=mbt_book')); ?>" class="add-new-book"><?php _e('Add New Book', 'mybooktable'); ?></a>
		</div>

		<div id="welcome-panel" class="welcome-panel">
			<div class="welcome-panel-content">
				<h3><?php _e('Welcome to MyBookTable!', 'mybooktable'); ?></h3>
				<p class="about-description"><?php _e('We\'ve assembled some links to get you started:', 'mybooktable'); ?></p>
				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column">
						<h4><?php _e('Next Steps', 'mybooktable'); ?></h4>
						<ul>
							<?php if(!mbt_get_setting('installed_examples')) { ?>
								<li><a href="<?php echo(admin_url('edit.php?post_type=mbt_book&mbt_install_examples=1')); ?>" class="welcome-icon"><?php _e('Look at some example Books', 'mybooktable'); ?></a></li>
							<?php } ?>
							<li><a href="<?php echo(admin_url('post-new.php?post_type=mbt_book')); ?>" class="welcome-icon welcome-add-page"><?php _e('Create your first book', 'mybooktable'); ?></a></li>
							<li><a href="<?php echo(mbt_get_booktable_url()); ?>" class="welcome-icon welcome-view-site"><?php _e('View your Book Table', 'mybooktable'); ?></a></li>
						</ul>
					</div>
					<div class="welcome-panel-column">
						<h4><?php _e('More Actions', 'mybooktable'); ?></h4>
						<ul>
							<li><div class="welcome-icon welcome-widgets-menus"><?php _e('Manage', 'mybooktable'); ?> <a href="<?php echo(admin_url('edit.php?post_type=mbt_book')); ?>"><?php _e('Books', 'mybooktable'); ?></a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus"><?php _e('Manage', 'mybooktable'); ?> <a href="<?php echo(admin_url('edit-tags.php?taxonomy=mbt_author')); ?>"><?php _e('Authors', 'mybooktable'); ?></a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus"><?php _e('Manage', 'mybooktable'); ?> <a href="<?php echo(admin_url('edit-tags.php?taxonomy=mbt_genre')); ?>"><?php _e('Genres', 'mybooktable'); ?></a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus"><?php _e('Manage', 'mybooktable'); ?> <a href="<?php echo(admin_url('edit-tags.php?taxonomy=mbt_series')); ?>"><?php _e('Series', 'mybooktable'); ?></a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus"><?php _e('Manage', 'mybooktable'); ?> <a href="<?php echo(admin_url('edit-tags.php?taxonomy=mbt_tag')); ?>"><?php _e('Tags', 'mybooktable'); ?></a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus"><?php _e('Manage', 'mybooktable'); ?> <a href="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>"><?php _e('Settings', 'mybooktable'); ?></a></div></li>
							<li><a href="<?php echo(admin_url('admin.php?page=mbt_help')); ?>" class="welcome-icon welcome-learn-more"><?php _e('Learn more about MyBookTable', 'mybooktable'); ?></a></li>
						</ul>
					</div>
					<div class="welcome-panel-column welcome-panel-last">
						<h4><?php _e('Extra Links', 'mybooktable'); ?></h4>
						<ul>
							<li><a href="<?php echo(admin_url('admin.php?page=mbt_dashboard&subpage=mbt_founders_page')); ?>" class="welcome-icon welcome-write-blog"><?php _e('View Founders List', 'mybooktable'); ?></a></li>
							<li><a href="http://authormedia.us1.list-manage.com/subscribe?u=b7358f48fe541fe61acdf747b&amp;id=6b5a675fcf" class="welcome-icon welcome-write-blog" target="_blank"><?php _e('Get Book Marketing Tips from Author Media', 'mybooktable'); ?></a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<div class="metabox-holder">
			<div id="mbt_dashboard_rss" class="postbox">
				<div class="handlediv" title=""><br></div>
				<h3 class="hndle"><?php _e('Book Marketing Tips from Author Media', 'mybooktable'); ?></h3>
				<div class="inside">
					<?php wp_widget_rss_output(array(
						'link' => 'http://www.authormedia.com/',
						'url' => 'http://www.authormedia.com/feed/',
						'title' => __('Recent News from Author Media', 'mybooktable'),
						'items' => 3,
						'show_summary' => 1,
						'show_author' => 0,
						'show_date' => 0,
					)); ?>
				</div>
			</div>
			<div id="mbt_dashboard_upsell" class="postbox">
				<div class="handlediv"><br></div>
				<h3 class="hndle"><?php _e('Current Version', 'mybooktable'); ?></h3>
				<div class="inside">
					<?php if(mbt_get_setting('dev_active')) { ?>
						<h1 class="currently-using pro"><?php _e('You are currently using', 'mybooktable'); ?> <span class="current-version"><?php _e('MyBookTable Developer', 'mybooktable'); ?> <?php echo(MBT_VERSION); ?></span></h1>
						<h2 class="thank-you"><?php _e('Thank you for your support!', 'mybooktable'); ?></h2>
					<?php } else if(mbt_get_setting('pro_active')) { ?>
						<h1 class="currently-using pro"><?php _e('You are currently using', 'mybooktable'); ?> <span class="current-version"><?php _e('MyBookTable Professional', 'mybooktable'); ?> <?php echo(MBT_VERSION); ?></span></h1>
						<h2 class="thank-you"><?php _e('Thank you for your support!', 'mybooktable'); ?></h2>
					<?php } else { ?>
						<h1 class="currently-using basic"><?php _e('You are currently using', 'mybooktable'); ?> <span class="current-version"><?php _e('MyBookTable Basic', 'mybooktable'); ?> <?php echo(MBT_VERSION); ?></span></h1>
						<h2 class="upgrade-title"><?php _e('Upgrade to MyBookTable Pro and get:', 'mybooktable'); ?></h2>
						<ul class="upgrade-list">
							<li><?php _e('Free Support', 'mybooktable'); ?></li>
							<li><?php _e('Amazon Affiliate Integration', 'mybooktable'); ?></li>
							<li><?php _e('Barnes &amp; Noble Support', 'mybooktable'); ?></li>
							<li><?php _e('Universal Buy Button', 'mybooktable'); ?></li>
							<li><a href="http://mybooktable.com" target="_blank"><?php _e('And much much more', 'mybooktable'); ?></a></li>
						</ul>
					<?php } ?>
				</div>
			</div>
		</div>

	</div>

<?php
}

function mbt_render_download_addons_page() {
?>
	<div class="wrap mbt_settings">
		<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('Download Addons', 'mybooktable'); ?></h2>

		<a href="<?php echo(admin_url('admin.php?page=mbt_dashboard')); ?>"><?php _e('Back to Dashboard', 'mybooktable'); ?></a>
	</div>
<?php
}

function mbt_render_setup_default_affiliates_page() {
?>
	<div class="wrap mbt_settings">
		<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('MyBookTable Settings', 'mybooktable'); ?></h2>

		<p style="font-size:16px;">
			<?php _e('MyBookTable comes with over a dozen buy buttons from stores around the web. Two of these buttons-- namely, the ones for Amazon and Barnes &amp; Noble-- use affiliate links. The revenue from these links is used to help support and improve the MyBookTable plugin. If you would like to use your own affiliate links, we have premium add-ons that come not only with affiliate integration but with premium support as well. You may also disable these buttons if you prefer.', 'mybooktable'); ?>
		</p>

		<form id="mbt_settings_form" method="post" action="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>">
			<input type="submit" name="save_default_affiliate_settings" id="submit" class="button button-primary" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=1&amp;mbt_enable_default_affiliates=1');" value="<?php _e('Enable Amazon and Barnes &amp; Noble Affiliate Buttons', 'mybooktable'); ?>">
			<input type="submit" name="save_default_affiliate_settings" id="submit" class="button button-primary" onclick="jQuery('#mbt_settings_form').attr('action', '<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=1&amp;mbt_disable_default_affiliates=1');" value="<?php _e('Disable Amazon and Barnes &amp; Noble Affiliate Buttons', 'mybooktable'); ?>">
			<a href="http://www.authormedia.com/mybooktable/add-ons" id="submit" class="button button-primary" target="_blank"><?php _e('Buy a Premium Add-On with Affiliate support', 'mybooktable'); ?></a>
		</form>
		<br>
		<a href="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;tab=1"><?php _e('Go to Affiliate Settings', 'mybooktable'); ?></a>
	</div>
<?php
}

function mbt_render_founders_page() {
?>

	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('MyBookTable Founders', 'mybooktable'); ?></h2>

		<p><?php _e('This plugin was made possible by some adventurous kickstarters. We are so grateful for the members of the writing community who backed our Kickstarter project and helped us launch this plugin! Below are the ones who sponsored at the $75 level or higher. Thank you for your support!', 'mybooktable'); ?></p>
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
			<li><a href="http://techguyjay.com/books" target="_blank">Jay Donovan</a></li>
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
