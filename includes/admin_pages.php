<?php

function mbt_admin_pages_init() {
	if(is_admin()) {
		add_action('admin_menu', 'mbt_add_admin_pages', 9);
		add_action('admin_enqueue_scripts', 'mbt_enqueue_admin_styles');
		add_action('admin_enqueue_scripts', 'mbt_enqueue_admin_js');
	}
}
add_action('mbt_init', 'mbt_admin_pages_init');

function mbt_enqueue_admin_styles() {
	wp_enqueue_style('mbt-admin-css', plugins_url('css/admin-style.css', dirname(__FILE__)));
	wp_enqueue_style('mbt-jquery-ui', plugins_url('css/jquery-ui.css', dirname(__FILE__)));
}

function mbt_enqueue_admin_js() {
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-widget');
	wp_enqueue_script('jquery-ui-position');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-slider');
	wp_enqueue_script('jquery-ui-accordion');
	wp_enqueue_script('jquery-ui-tooltip', plugins_url('js/lib/jquery.ui.tooltip.js', dirname(__FILE__)), array('jquery-ui-widget'));

	wp_enqueue_script('mbt-admin-pages', plugins_url('js/admin.js', dirname(__FILE__)), array('jquery'));
	wp_localize_script('mbt-admin-pages', 'mbt_media_upload_i18n', array(
		'mbt_upload_sample_button' => __('Sample Chapter Image', 'mybooktable'),
		'mbt_upload_tax_image_button' => __('Taxonomy Image', 'mybooktable'),
		'mbt_set_book_image_button' => __('Book Cover Image', 'mybooktable'),
		'select' => __('Select', 'mybooktable')
	));

	if(function_exists('wp_enqueue_media')) { wp_enqueue_media(); }
}

function mbt_add_admin_pages() {
	add_menu_page(__('MyBookTable', 'mybooktable'), __('MyBookTable', 'mybooktable'), 'edit_posts', 'mbt_dashboard', 'mbt_render_dashboard', 'dashicons-book-alt', '10.7');
	add_submenu_page('mbt_dashboard', __('Books', 'mybooktable'), __('Books', 'mybooktable'), 'edit_posts', 'edit.php?post_type=mbt_book');
	add_submenu_page('mbt_dashboard', __('Add Book', 'mybooktable'), __('Add Book', 'mybooktable'), 'edit_posts', 'post-new.php?post_type=mbt_book');
	add_submenu_page('mbt_dashboard', __('Authors', 'mybooktable'), __('Authors', 'mybooktable'), 'manage_categories', 'edit-tags.php?taxonomy=mbt_author&amp;post_type=mbt_book');
	add_submenu_page('mbt_dashboard', __('Genres', 'mybooktable'), __('Genres', 'mybooktable'), 'manage_categories', 'edit-tags.php?taxonomy=mbt_genre&amp;post_type=mbt_book');
	add_submenu_page('mbt_dashboard', __('Series', 'mybooktable'), __('Series', 'mybooktable'), 'manage_categories', 'edit-tags.php?taxonomy=mbt_series&amp;post_type=mbt_book');
	add_submenu_page('mbt_dashboard', __('Tags', 'mybooktable'), __('Tags', 'mybooktable'), 'manage_categories', 'edit-tags.php?taxonomy=mbt_tag&amp;post_type=mbt_book');
	add_submenu_page('mbt_dashboard', __('Import Books', 'mybooktable'), __('Import Books', 'mybooktable'), 'edit_posts', 'mbt_import', 'mbt_render_import_page');
	add_submenu_page('mbt_dashboard', __('MyBookTable Settings', 'mybooktable'), __('Settings', 'mybooktable'), 'manage_options', 'mbt_settings', 'mbt_render_settings_page');
	add_submenu_page('mbt_dashboard', __('MyBookTable Help', 'mybooktable'), __('Help', 'mybooktable'), 'edit_posts', 'mbt_help', 'mbt_render_help_page');

	if(mbt_get_upgrade() === false) {
		$page_hook = add_submenu_page('mbt_dashboard', __('Upgrade', 'mybooktable'), __('Upgrade MyBookTable', 'mybooktable'), 'edit_posts', 'mbt_upgrade_link', ' ');
		add_action('load-'.$page_hook, 'mbt_upgrade_link_redirect');
	}

	remove_menu_page("edit.php?post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit.php?post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "post-new.php?post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_author&amp;post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_genre&amp;post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_series&amp;post_type=mbt_book");
	remove_submenu_page("edit.php?post_type=mbt_book", "edit-tags.php?taxonomy=mbt_tag&amp;post_type=mbt_book");
}

function mbt_upgrade_link_redirect() {
	wp_redirect('https://www.authormedia.com/mybooktable', 301);
	exit();
}



/*---------------------------------------------------------*/
/* Ajax Event Tracking                                     */
/*---------------------------------------------------------*/

function mbt_ajax_event_tracking_init() {
	add_action('wp_ajax_mbt_track_event', 'mbt_track_event_ajax');
}
add_action('mbt_init', 'mbt_ajax_event_tracking_init');

function mbt_track_event_ajax() {
	if(!empty($_REQUEST['event_name'])) {
		mbt_track_event($_REQUEST['event_name']);
	}
	die();
}



/*---------------------------------------------------------*/
/* Settings Page                                           */
/*---------------------------------------------------------*/

function mbt_settings_page_init() {
	add_action('wp_ajax_mbt_api_key_refresh', 'mbt_api_key_refresh_ajax');
	add_action('wp_ajax_mbt_style_pack_preview', 'mbt_style_pack_preview_ajax');
	add_action('wp_ajax_mbt_button_size_preview', 'mbt_button_size_preview_ajax');

	//needs to happen before setup.php admin_init in order to properly update admin notices
	add_action('admin_init', 'mbt_save_settings_page');
}
add_action('mbt_init', 'mbt_settings_page_init');

function mbt_save_settings_page() {
	if(isset($_REQUEST['page']) and $_REQUEST['page'] == 'mbt_settings' and isset($_REQUEST['save_settings'])) {
		do_action('mbt_settings_save');

		if($_REQUEST['mbt_api_key'] != mbt_get_setting('api_key')) {
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
		mbt_update_setting('reviews_box', $_REQUEST['mbt_reviews_box']);
		mbt_update_setting('enable_buybutton_shadowbox', isset($_REQUEST['mbt_enable_buybutton_shadowbox']));
		mbt_update_setting('enable_breadcrumbs', isset($_REQUEST['mbt_enable_breadcrumbs']));
		mbt_update_setting('show_series', isset($_REQUEST['mbt_show_series']));
		mbt_update_setting('show_find_bookstore', isset($_REQUEST['mbt_show_find_bookstore']));
		mbt_update_setting('hide_domc_notice', isset($_REQUEST['mbt_hide_domc_notice']));
		mbt_update_setting('domc_notice_text', wp_unslash($_REQUEST['mbt_domc_notice_text']));
		mbt_update_setting('posts_per_page', $_REQUEST['mbt_posts_per_page']);
		mbt_update_setting('book_button_size', $_REQUEST['mbt_book_button_size']);
		mbt_update_setting('listing_button_size', $_REQUEST['mbt_listing_button_size']);
		mbt_update_setting('widget_button_size', $_REQUEST['mbt_widget_button_size']);

		$settings_updated = true;
	} else if(isset($_REQUEST['page']) and $_REQUEST['page'] == 'mbt_settings' and isset($_REQUEST['save_default_affiliate_settings'])) {
		if(isset($_REQUEST['mbt_enable_default_affiliates']) and !empty($_REQUEST['mbt_enable_default_affiliates'])) {
			mbt_update_setting('enable_default_affiliates', $_REQUEST['mbt_enable_default_affiliates'] === 'true');
		}
	}

	if(isset($_REQUEST['mbt_remove_booktable_page'])) { mbt_update_setting('booktable_page', 0); }
}

function mbt_api_key_refresh_ajax() {
	if(!current_user_can('manage_options')) { die(); }
	mbt_update_setting('api_key', $_REQUEST['data']);
	mbt_verify_api_key();
	echo(mbt_api_key_feedback());
	die();
}

function mbt_api_key_feedback() {
	$output = '';
	if(mbt_get_setting('api_key') and mbt_get_setting('api_key_status') != 0) {
		if(mbt_get_setting('api_key_status') > 0) {
			$output .= '<span class="mbt_admin_message_success">'.__('Valid API Key', 'mybooktable').': '.mbt_get_setting('api_key_message').'</span>';
			$upgrade_message = mbt_get_upgrade_message(null, '');
			if(!empty($upgrade_message)) { $output .= '<br>'.$upgrade_message; }
		} else {
			$output .= '<span class="mbt_admin_message_failure">'.__('Invalid API Key', 'mybooktable').': '.mbt_get_setting('api_key_message').'</span>';
		}
	}
	return $output;
}

function mbt_style_pack_preview_ajax() {
	echo('<img src="'.mbt_style_url('amazon_button.png',  $_REQUEST['data']).'">');
	die();
}

function mbt_button_size_preview_ajax() {
	mbt_button_size_feedback($_REQUEST['data']);
	die();
}

function mbt_button_size_feedback($size) {
	$id = 'mbt_book_'.time().'_'.rand();
	echo('<img id="'.$id.'" src="'.mbt_style_url('amazon_button.png', 'Default').'">');
	echo('<style type="text/css">');
	if($size == 'small') { echo('#'.$id.' { width: 144px; height: 25px; }'); }
	else if($size == 'medium') { echo('#'.$id.' { width: 172px; height: 30px; }'); }
	else { echo('#'.$id.' { width: 201px; height: 35px; }'); }
	echo('</style>');
}

function mbt_render_settings_page() {
	mbt_track_event('view_settings_page');
	if(!empty($_GET['mbt_setup_default_affiliates'])) { return mbt_render_setup_default_affiliates_page(); }
?>
	<div class="wrap mbt_settings">
		<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('MyBookTable Settings', 'mybooktable'); ?></h2>
		<?php if(!empty($settings_updated)) { ?>
			<div id="setting-error-settings_updated" class="updated settings-error"><p><strong><?php _e('Settings saved', 'mybooktable'); ?>.</strong></p></div>
		<?php } ?>

		<form id="mbt_settings_form" method="post" action="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>">
			<input type="hidden" name="mbt_current_tab" id="mbt_current_tab" value="<?php echo(isset($_REQUEST['mbt_current_tab'])?$_REQUEST['mbt_current_tab']:1); ?>">

			<div id="mbt-tabs">
				<ul>
					<li><a href="#mbt-tab-1" data-mbt-track-event="settings_page_setup_tab_click"><?php _e('Setup', 'mybooktable'); ?></a></li>
					<li><a href="#mbt-tab-2" data-mbt-track-event="settings_page_style_tab_click"><?php _e('Style', 'mybooktable'); ?></a></li>
					<li><a href="#mbt-tab-3" data-mbt-track-event="settings_page_promote_tab_click"><?php _e('Promote', 'mybooktable'); ?></a></li>
					<li><a href="#mbt-tab-4" data-mbt-track-event="settings_page_affiliates_tab_click"><?php if(mbt_get_ab_testing_status('settings_page_affiliates_tab')) { _e('Affiliates', 'mybooktable'); } else { _e('Earn', 'mybooktable'); } ?></a></li>
					<li><a href="#mbt-tab-5" data-mbt-track-event="settings_page_integrate_tab_click"><?php _e('Integrate', 'mybooktable'); ?></a></li>
					<li><a href="<?php echo(admin_url('admin.php?page=mbt_help')); ?>" id="mbt-help-link" data-mbt-track-event-override="settings_page_help_tab_click"><?php if(mbt_get_ab_testing_status('settings_page_help_tab')) { _e('Help', 'mybooktable'); } else { _e('Troubleshoot', 'mybooktable'); } ?></a></li>
				</ul>
				<div class="mbt-tab" id="mbt-tab-1">
					<table class="form-table">
						<tbody>
							<tr>
								<th><?php _e('MyBookTable API Key', 'mybooktable'); ?></th>
								<td>
									<div class="mbt_api_key_feedback mbt_feedback"><?php echo(mbt_api_key_feedback()); ?></div>
									<div style="clear:both"></div>
									<input type="text" name="mbt_api_key" id="mbt_api_key" value="<?php echo(mbt_get_setting('api_key')); ?>" size="60" class="regular-text" />
									<div class="mbt_feedback_refresh" data-refresh-action="mbt_api_key_refresh" data-element="mbt_api_key"></div>
									<p class="description"><?php _e('If you have purchased an API Key for MyBookTable, enter it here to activate your enhanced features. You can find it in your <a href="https://gumroad.com/library/" target="_blank">Gumroad Library here</a>. If you would like to purchase an API key visit <a href="http://www.authormedia.com/mybooktable/">AuthorMedia.com/MyBookTable</a>.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr>
								<th><?php _e('Book Table Page', 'mybooktable'); ?></th>
								<td>
									<select name="mbt_booktable_page" id="mbt_booktable_page">
										<option value="0" <?php selected(mbt_get_setting('booktable_page'), 0); ?> ><?php _e('-- Choose One --', 'mybooktable');?></option>
										<?php foreach(get_pages() as $page) { ?>
											<option value="<?php echo($page->ID); ?>" <?php selected(mbt_get_setting('booktable_page'), $page->ID); ?> ><?php echo($page->post_title); ?></option>
										<?php } ?>
									</select>
									<?php if(mbt_get_setting('booktable_page') == 0 or !get_page(mbt_get_setting('booktable_page'))) { ?>
										<a href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_install_pages=1')); ?>" class="button button-primary"><?php _e('Click here to create a Book Table page', 'mybooktable'); ?></a>
									<?php } else { ?>
										<a href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_remove_booktable_page=1')); ?>" class="button button-primary"><?php _e('Remove Book Table page', 'mybooktable'); ?></a>
									<?php } ?>
									<p class="description"><?php _e('The Book Table page is the main landing page for your books.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<?php if(!mbt_get_setting('installed_examples')) { ?>
								<tr>
									<th><?php _e('Example Books', 'mybooktable'); ?></th>
									<td>
										<a href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_install_examples=1')); ?>" class="button button-primary"><?php _e('Click here to create example books', 'mybooktable'); ?></a>
										<p class="description"><?php _e('These examples will help you learn how to set up Genres, Series, Authors, and Books of your own.', 'mybooktable'); ?></p>
									</td>
								</tr>
							<?php } ?>
							<tr>
								<th><label for="mbt_compatibility_mode"><?php _e('Compatability Mode', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_compatibility_mode" id="mbt_compatibility_mode" <?php checked(mbt_get_setting('compatibility_mode'), true); ?> >
									<p class="description"><?php _e('Checked = More Compatible Out of the Box. Unchecked = More Developer Control.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr>
								<th><?php _e('MyBookTable Product Name', 'mybooktable'); ?></th>
								<td>
									<input type="text" name="mbt_product_name" id="mbt_product_name" value="<?php echo(mbt_get_setting('product_name')); ?>" size="60" class="regular-text" />
									<p class="description"><?php _e('You can use this to change the "books" slug used in the book page urls if you are selling something other than books, such as "DVDs", "Movies", or simply "Products".', 'mybooktable'); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					<?php do_action("mbt_setup_settings_render"); ?>
					<input type="submit" name="save_settings" class="button button-primary" value="<?php _e('Save Changes', 'mybooktable'); ?>">
				</div>
				<div class="mbt-tab" id="mbt-tab-2">
					<div class="mbt-section">
						<div class="mbt-section-header">Buy Button Styles</div>
						<div class="mbt-section-content">
							<table class="form-table">
								<tbody>
									<tr>
										<th><label for="mbt_style_pack"><?php _e('Style Pack', 'mybooktable'); ?></label></th>
										<td colspan="3">
											<?php $pack_upload_output = mbt_do_style_pack_upload(); ?>
											<?php $current_style = mbt_get_setting('style_pack'); ?>
											<div id="mbt_style_pack_preview" class="mbt_feedback"><img src="<?php echo(mbt_style_url('amazon_button.png', $current_style)); ?>"></div>
											<select name="mbt_style_pack" id="mbt_style_pack" class="mbt_feedback_refresh" data-refresh-action="mbt_style_pack_preview" data-element="mbt_style_pack">
												<option value="Default" <?php echo((empty($current_style) or $current_style == 'Default') ? ' selected="selected"' : '') ?> ><?php _e('Default', 'mybooktable'); ?></option>
												<?php foreach(mbt_get_style_packs() as $style) { ?>
													<option value="<?php echo($style); ?>" <?php echo($current_style == $style ? ' selected="selected"' : ''); ?> ><?php echo($style); ?></option>
												<?php } ?>
											</select>
											<input type="hidden" id="mbt_style_pack_id" name="mbt_style_pack_id" onchange="jQuery('#mbt_current_tab').val(2); jQuery('#mbt_settings_form').submit();">
											<input id="mbt_upload_style_pack_button" type="button" class="button" value="<?php _e('Upload New Style Pack', 'mybooktable'); ?>">
											<?php echo($pack_upload_output); ?>
											<p class="description"><?php _e('Choose the style pack you would like for your buy buttons.', 'mybooktable'); ?></p>
										</td>
									</tr>
									<tr>
										<th><?php _e('Buy Button Sizes', 'mybooktable'); ?></th>
										<?php $button_sizes = array('small' =>__('Small', 'mybooktable'), 'medium' => __('Medium', 'mybooktable'), 'large' => __('Large', 'mybooktable')); ?>
										<td>
											<h4><label for="mbt_book_button_size"><?php _e('Book Pages', 'mybooktable'); ?></label></h4>
											<?php $book_button_size = mbt_get_setting('book_button_size'); ?>
											<?php if(empty($book_button_size)) { $book_button_size = 'medium'; } ?>
											<div id="mbt_book_button_size_preview" class="mbt_feedback"><?php mbt_button_size_feedback($book_button_size); ?></div>
											<?php foreach($button_sizes as $size => $size_name) { ?>
												<input type="radio" name="mbt_book_button_size" id="mbt_book_button_size_<?php echo($size); ?>" value="<?php echo($size); ?>" <?php checked($book_button_size, $size); ?>
												class="mbt_feedback_refresh" data-refresh-action="mbt_button_size_preview" data-element="mbt_book_button_size_<?php echo($size); ?>"><?php echo($size_name); ?><br>
											<?php } ?>
											<p class="description"><?php _e('Select the size of the buy buttons on book pages.', 'mybooktable'); ?></p>
										</td>
										<td>
											<h4><label for="mbt_book_button_size"><?php _e('Book Listings', 'mybooktable'); ?></label></h4>
											<?php $listing_button_size = mbt_get_setting('listing_button_size'); ?>
											<?php if(empty($listing_button_size)) { $listing_button_size = 'medium'; } ?>
											<div id="mbt_listing_button_size_preview" class="mbt_feedback"><?php mbt_button_size_feedback($listing_button_size); ?></div>
											<?php foreach($button_sizes as $size => $size_name) { ?>
												<input type="radio" name="mbt_listing_button_size" id="mbt_listing_button_size_<?php echo($size); ?>" value="<?php echo($size); ?>" <?php checked($listing_button_size, $size); ?>
												class="mbt_feedback_refresh" data-refresh-action="mbt_button_size_preview" data-element="mbt_listing_button_size_<?php echo($size); ?>"><?php echo($size_name); ?><br>
											<?php } ?>
											<p class="description"><?php _e('Select the size of the buy buttons on book listings.', 'mybooktable'); ?></p>
										</td>
										<td>
											<h4><label for="mbt_book_button_size"><?php _e('Widgets', 'mybooktable'); ?></label></h4>
											<?php $widget_button_size = mbt_get_setting('widget_button_size'); ?>
											<?php if(empty($widget_button_size)) { $widget_button_size = 'medium'; } ?>
											<div id="mbt_widget_button_size_preview" class="mbt_feedback"><?php mbt_button_size_feedback($widget_button_size); ?></div>
											<?php foreach($button_sizes as $size => $size_name) { ?>
												<input type="radio" name="mbt_widget_button_size" id="mbt_widget_button_size_<?php echo($size); ?>" value="<?php echo($size); ?>" <?php checked($widget_button_size, $size); ?>
												class="mbt_feedback_refresh" data-refresh-action="mbt_button_size_preview" data-element="mbt_widget_button_size_<?php echo($size); ?>"><?php echo($size_name); ?><br>
											<?php } ?>
											<p class="description"><?php _e('Select the size of the buy buttons on widgets.', 'mybooktable'); ?></p>
										</td>
									</tr>
									<tr>
										<th><label for="mbt_enable_buybutton_shadowbox"><?php _e('Buy Button Shadow Box', 'mybooktable'); ?></label></th>
										<td colspan="3">
											<input type="checkbox" name="mbt_enable_buybutton_shadowbox" id="mbt_enable_buybutton_shadowbox" <?php checked(mbt_get_setting('enable_buybutton_shadowbox'), true); ?> >
											<label for="mbt_enable_buybutton_shadowbox"><?php _e('Enable', 'mybooktable'); ?></label>
											<p class="description"><?php _e('Replace store buy buttons with a single "Buy Now" button that loads a shadow box with all the buttons within it.', 'mybooktable'); ?></p>
										</td>
									</tr>
								</tbody>
							</table>
							<?php do_action("mbt_buybutton_style_settings_render"); ?>
						</div>
					</div>
					<div class="mbt-section">
						<div class="mbt-section-header">General Styles</div>
						<div class="mbt-section-content">
							<table class="form-table">
								<tbody>
									<tr>
										<th><label for="mbt_image_size"><?php _e('Book Image Size', 'mybooktable'); ?></label></th>
										<td>
											<?php $image_sizes = array('small' =>__('Small', 'mybooktable'), 'medium' => __('Medium', 'mybooktable'), 'large' => __('Large', 'mybooktable')); ?>
											<?php $image_size = mbt_get_setting('image_size'); ?>
											<?php if(empty($image_size)) { $image_size = 'medium'; } ?>
											<?php foreach($image_sizes as $size => $size_name) { ?>
												<input type="radio" name="mbt_image_size" value="<?php echo($size); ?>" <?php checked($image_size, $size); ?> ><?php echo($size_name); ?><br>
											<?php } ?>
											<p class="description"><?php _e('Book Images in MyBookTable respond to mobile devices regardless of which size you select.', 'mybooktable'); ?></p>
										</td>
									</tr>
									<tr>
										<th><label for="mbt_enable_breadcrumbs"><?php _e('Breadcrumbs', 'mybooktable'); ?></label></th>
										<td>
											<input type="checkbox" name="mbt_enable_breadcrumbs" id="mbt_enable_breadcrumbs" <?php checked(mbt_get_setting('enable_breadcrumbs'), true); ?> >
											<label for="mbt_enable_breadcrumbs"><?php _e('Enable', 'mybooktable'); ?></label>
											<p class="description"><?php _e('Breadcrumbs make your website easier to navigate for both humans and search engines. Uncheck this box if MyBookTable\'s breadcrumb system is conflicting with your theme.', 'mybooktable'); ?></p>
										</td>
									</tr>
								</tbody>
							</table>
							<?php do_action("mbt_general_style_settings_render"); ?>
						</div>
					</div>
					<div class="mbt-section">
						<div class="mbt-section-header">Book Listing Styles</div>
						<div class="mbt-section-content">
							<table class="form-table">
								<tbody>
									<tr>
										<th><label for="mbt_posts_per_page"><?php _e('Number of Books per Page', 'mybooktable'); ?></label></th>
										<td>
											<input name="mbt_posts_per_page" type="text" id="mbt_posts_per_page" value="<?php echo(mbt_get_setting('posts_per_page') ? mbt_get_setting('posts_per_page') : get_option('posts_per_page')); ?>" class="regular-text">
											<p class="description"><?php _e('Choose the number of books to show per page on the book listings.', 'mybooktable'); ?>.</p>
										</td>
									</tr>
								</tbody>
							</table>
							<?php do_action("mbt_listings_style_settings_render"); ?>
						</div>
					</div>
					<input type="submit" name="save_settings" class="button button-primary" value="<?php _e('Save Changes', 'mybooktable'); ?>">
				</div>
				<div class="mbt-tab" id="mbt-tab-3">
					<table class="form-table">
						<tbody>
							<tr>
								<th><label for="mbt_enable_seo"><?php _e('Search Engine Optimization', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_enable_seo" id="mbt_enable_seo" <?php echo(mbt_get_setting('enable_seo') ? ' checked="checked"' : ''); ?> >
									<label for="mbt_enable_seo"><?php _e('Use MyBookTable\'s built-in SEO features', 'mybooktable'); ?></label>
									<p class="description"><?php _e('Let MyBookTable\'s built in search engine optimization do the work for you.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="mbt_image_size"><?php _e('Book Reviews Box', 'mybooktable'); ?></label></th>
								<td>
									<?php
										$reviews_boxes = mbt_get_reviews_boxes();
										$current_reviews = mbt_get_setting('reviews_box');
										if(empty($current_reviews) or empty($reviews_boxes[$current_reviews])) { $current_reviews = 'none'; }
										echo('<input type="radio" name="mbt_reviews_box" id="mbt_reviews_box_none" value="none" '.checked($current_reviews, 'none', false).'><label for="mbt_reviews_box_none">None</label><br>');
										foreach($reviews_boxes as $slug => $reviews_data) {
											if(!empty($reviews_data['disabled'])) {
												echo('<input type="radio" name="mbt_reviews_box" id="mbt_reviews_box_'.$slug.'" value="'.$slug.'" '.checked($current_reviews, $slug, false).' disabled="disabled">');
												echo('<label for="mbt_reviews_box_'.$slug.'" class="mbt_reviews_box_disabled">'.$reviews_data['name'].' ('.$reviews_data['disabled'].')</label><br>');
											} else {
												echo('<input type="radio" name="mbt_reviews_box" id="mbt_reviews_box_'.$slug.'" value="'.$slug.'" '.checked($current_reviews, $slug, false).'>');
												echo('<label for="mbt_reviews_box_'.$slug.'">'.$reviews_data['name'].'</label><br>');
											}
										}
									?>
									<p class="description"><?php _e('Select the reviews box that will be displayed under each book with a valid ISBN.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="mbt_enable_socialmedia_badges_single_book"><?php _e('Social Media Badges', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_enable_socialmedia_badges_single_book" id="mbt_enable_socialmedia_badges_single_book" <?php checked(mbt_get_setting('enable_socialmedia_badges_single_book'), true); ?> >
									<label for="mbt_enable_socialmedia_badges_single_book"><?php _e('Show on Book Pages', 'mybooktable'); ?></label><br>
									<input type="checkbox" name="mbt_enable_socialmedia_badges_book_excerpt" id="mbt_enable_socialmedia_badges_book_excerpt" <?php checked(mbt_get_setting('enable_socialmedia_badges_book_excerpt'), true); ?> >
									<label for="mbt_enable_socialmedia_badges_book_excerpt"><?php _e('Show on Book Listings', 'mybooktable'); ?></label>
									<p class="description"><?php _e('Check to enable MyBookTable\'s social media badges.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="mbt_enable_socialmedia_bar_single_book"><?php _e('Social Media Bar', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_enable_socialmedia_bar_single_book" id="mbt_enable_socialmedia_bar_single_book" <?php checked(mbt_get_setting('enable_socialmedia_bar_single_book'), true); ?> >
									<label for="mbt_enable_socialmedia_bar_single_book"><?php _e('Show on Book Pages', 'mybooktable'); ?></label>
									<p class="description"><?php _e('Check to enable the social media bar on book pages.', 'mybooktable'); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="mbt_show_series"><?php _e('Cross Promote Books in a Series', 'mybooktable'); ?></label></th>
								<td>
									<input type="checkbox" name="mbt_show_series" id="mbt_show_series" <?php checked(mbt_get_setting('show_series'), true); ?> >
									<label for="mbt_show_series"><?php _e('Show books', 'mybooktable'); ?></label>
									<p class="description"><?php _e('If checked, the other books in the same series will display under the book on that book\'s page.', 'mybooktable'); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					<?php do_action("mbt_promote_settings_render"); ?>
					<input type="submit" name="save_settings" class="button button-primary" value="<?php _e('Save Changes', 'mybooktable'); ?>">
				</div>
				<div class="mbt-tab" id="mbt-tab-4">
					<?php do_action("mbt_affiliate_settings_render"); ?>
					<table class="form-table">
						<tbody>
							<tr>
								<th><label for="mbt_hide_domc_notice"><?php _e('Disclosure of Material Connection Disclaimer', 'mybooktable'); ?></label></th>
								<td>
									<textarea rows="5" cols="60" name="mbt_domc_notice_text" id="mbt_domc_notice_text"><?php echo(mbt_get_setting('domc_notice_text')); ?></textarea>
									<p class="description">
										<input type="checkbox" name="mbt_hide_domc_notice" id="mbt_hide_domc_notice" <?php checked(mbt_get_setting('hide_domc_notice'), true); ?> >
										<?php _e('Hide Disclosure of Material Connection Disclaimer?', 'mybooktable'); ?>
									</p>
								</td>
							</tr>
						</tbody>
					</table>
					<?php
						if(mbt_get_upgrade() === false) {
							echo('<div class="mbt-default-affiliates">');
							if(mbt_get_setting('enable_default_affiliates')) {
								printf(__('Amazon and Barnes &amp; Noble Buy Buttons enabled! <a href="%s" target="_blank">Disable</a>', 'mybooktable'), admin_url('admin.php?page=mbt_settings&mbt_setup_default_affiliates=1'));
							} else {
								printf(__('Amazon and Barnes &amp; Noble Buy Buttons disabled! <a href="%s" target="_blank">Enable</a>', 'mybooktable'), admin_url('admin.php?page=mbt_settings&mbt_setup_default_affiliates=1'));
							}
							echo('<a href="'.admin_url('admin.php?page=mbt_settings&mbt_setup_default_affiliates=1').'" class="mbt-default-affiliates-small" target="_blank">'.__('What does this mean?', 'mybooktable').'</a>');
							echo('</div>');
						}
					?>
					<input type="submit" name="save_settings" class="button button-primary" value="<?php _e('Save Changes', 'mybooktable'); ?>">
				</div>
				<div class="mbt-tab" id="mbt-tab-5">
					<?php do_action("mbt_integrate_settings_render"); ?>
					<input type="submit" name="save_settings" class="button button-primary" value="<?php _e('Save Changes', 'mybooktable'); ?>">
				</div>
			</div>

		</form>

	</div>
<?php
}

function mbt_mailchimp_api_key_settings_render() {
	?>
	<table class="form-table">
		<tbody>
			<tr>
				<th style="color: #666"><?php _e('MailChimp', 'mybooktable'); ?></th>
				<td>
					<input type="text" disabled="true" value="" class="regular-text">
					<p class="description"><?php echo(mbt_get_upgrade_message()); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}
add_action('mbt_integrate_settings_render', 'mbt_mailchimp_api_key_settings_render');

function mbt_amazon_web_services_general_settings_render() {
	?>
	<table class="form-table">
		<tbody>
			<tr>
				<th style="color: #666"><?php _e('Amazon Web Services', 'mybooktable'); ?></th>
				<td>
					<input type="text" disabled="true" value="" class="regular-text">
					<p class="description"><?php echo(mbt_get_upgrade_message()); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}
add_action('mbt_integrate_settings_render', 'mbt_amazon_web_services_general_settings_render');

function mbt_render_setup_default_affiliates_page() {
?>
	<div class="wrap mbt_settings">
		<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('MyBookTable Settings', 'mybooktable'); ?></h2>

		<p style="font-size:16px;">
			<?php _e('MyBookTable comes with over a dozen buy buttons from stores around the web. Several of these buttons, including the ones for Amazon and Barnes &amp; Noble, use affiliate links. The revenue from these links is used to help support and improve the MyBookTable plugin. If you would like to use your own affiliate links, we have premium upgrades that come not only with affiliate integration but with premium support as well. You may also disable these buttons if you prefer.', 'mybooktable'); ?>
		</p>

		<form id="mbt_settings_form" method="post" action="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>">
			<input type="hidden" name="mbt_enable_default_affiliates" id="mbt_enable_default_affiliates" value="">
			<input type="submit" name="save_default_affiliate_settings" class="button button-primary" onclick="jQuery('#mbt_enable_default_affiliates').val('true');" value="<?php _e('Enable Affiliate Buttons', 'mybooktable'); ?>">
			<input type="submit" name="save_default_affiliate_settings" class="button button-primary" onclick="jQuery('#mbt_enable_default_affiliates').val('false');" value="<?php _e('Disable Affiliate Buttons', 'mybooktable'); ?>">
			<a href="http://www.authormedia.com/products/mybooktable/upgrades/" class="button button-primary" target="_blank"><?php _e('Buy a Premium Upgrade with Affiliate support', 'mybooktable'); ?></a>
		</form>
		<br>
		<a href="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>&amp;mbt_current_tab=4"><?php _e('Go to Affiliate Settings', 'mybooktable'); ?></a>
	</div>
<?php
}

function mbt_do_style_pack_upload() {
	if(empty($_REQUEST['mbt_style_pack_id'])) { return ""; }
	$file_post = get_post($_REQUEST['mbt_style_pack_id']);
	if(empty($file_post)) { return ""; }
	$style_name = $file_post->post_title;
	$file_path = get_post_meta($_REQUEST['mbt_style_pack_id'], '_wp_attached_file', true);

	$nonce_url = wp_nonce_url('admin.php?page=mbt_settings', 'mbt-style-pack-upload');
	$output = mbt_get_wp_filesystem($nonce_url);
	if(!empty($output)) { return '<br'.$output;	}

	global $wp_filesystem;

	$upload_dir = wp_upload_dir();
	if(substr($upload_dir['basedir'], 0, strlen(ABSPATH)) !== ABSPATH) {
		return '<br><span class="mbt_admin_message_failure">'.__('Path error while adding style pack!', 'mybooktable').'</span>';
	}
	$content_prefix = substr($upload_dir['basedir'], strlen(ABSPATH));
	$from = $upload_dir['basedir'].DIRECTORY_SEPARATOR.$file_path;
	$to = $wp_filesystem->abspath().$content_prefix.DIRECTORY_SEPARATOR.'mbt_styles'.DIRECTORY_SEPARATOR.$style_name;
	$result = unzip_file($from, $to);

	if($result === true) {
		return '<br><span class="mbt_admin_message_success">'.__('Successfully added button pack!', 'mybooktable').'</span>';
	} else {
		return '<br><span class="mbt_admin_message_failure">'.__('Error unzipping style pack!', 'mybooktable').'</span>';
	}
}

function mbt_render_help_page() {
	mbt_track_event('view_help_page');
?>
	<div class="wrap mbt_help">
		<div id="icon-options-general" class="icon32"><br></div><h2 class="mbt_help_title"><?php _e('MyBookTable Help', 'mybooktable'); ?></h2>

		<div class="mbt_help_top_links">
			<a class="mbt_help_link mbt_apikey" href="https://gumroad.com/library/" target="_blank" data-mbt-track-event="help_page_apikey_button_click">
				<div class="mbt_icon"></div>Need to find or manage your <strong>API Key</strong>?<br>Access through Gumroad
			</a>
			<a class="mbt_help_link mbt_forum" href="http://wordpress.org/support/plugin/mybooktable" target="_blank" data-mbt-track-event="help_page_forum_button_click">
				<div class="mbt_icon"></div>Have <strong>questions or comments</strong>?<br>Check out the Support Forum
			</a>
			<a class="mbt_help_link mbt_develop" href="https://github.com/authormedia/mybooktable/wiki" target="_blank" data-mbt-track-event="help_page_develop_button_click">
				<div class="mbt_icon"></div>Looking for <strong>developer documentation</strong>?<br>Find it on Github
			</a>
			<div style="clear:both"></div>
		</div>

		<?php if(mbt_get_upgrade() === false) { ?>
			<div class="mbt_get_premium_support"><a href="http://www.authormedia.com/mybooktable/upgrades/" class="button button-primary"><?php _e('Need Premium Support? Purchase an upgrade here', 'mybooktable'); ?></a></div>
		<?php } else { ?>
			<div class="mbt_help_box">
				<div class="mbt_help_box_title"><?php _e('Premium Support Options', 'mybooktable'); ?></div>
				<div class="mbt_help_box_content">
					<ul class="mbt_premium_support">
						<li><a href="http://authormedia.freshdesk.com/support/tickets/new" target="_blank" data-mbt-track-event="help_page_support_ticket_click"><div class="mbt_icon mbt_ticket"></div><?php _e('Submit a Ticket', 'mybooktable'); ?></a></li>
						<li><a href="http://authormedia.freshdesk.com/support/discussions" target="_blank" data-mbt-track-event="help_page_support_forum_click"><div class="mbt_icon mbt_support"></div><?php _e('Visit the Support Forum', 'mybooktable'); ?></a></li>
						<li><a href="http://authormedia.freshdesk.com/support/discussions/topics/new" target="_blank" data-mbt-track-event="help_page_support_suggest_feature_click"><div class="mbt_icon mbt_feature"></div><?php _e('Suggest a Feature', 'mybooktable'); ?></a></li>
						<li><a href="http://authormedia.freshdesk.com/support/tickets/new" target="_blank" data-mbt-track-event="help_page_support_submit_bug_click"><div class="mbt_icon mbt_bug"></div><?php _e('Submit a Bug', 'mybooktable'); ?></a><br></li>
						<div style="clear:both"></div>
					</ul>
				</div>
			</div>
		<?php } ?>

		<?php
			$mybooktable_articles = array(
				'goodreads' => array(
					'link' => 'http://www.authormedia.com/how-to-add-goodreads-book-reviews-to-mybooktable/',
					'img' => plugins_url('images/help/goodreads-reviews.jpg', dirname(__FILE__)),
					'title' => __('How to Add GoodReads Book Reviews to MyBookTable', 'mybooktable')
				),
			);
		?>

		<div class="mbt_help_box">
			<div class="mbt_help_box_title"><?php _e('MyBookTable Tutorials', 'mybooktable'); ?></div>
			<div class="mbt_help_box_content">
				<ul class="mbt_articles">
					<?php foreach ($mybooktable_articles as $id => $article) { ?>
						<li>
							<a href="<?php echo($article['link']); ?>" target="_blank" data-mbt-track-event="help_page_mybooktable_article_click_<?php echo($id); ?>">
								<img src="<?php echo($article['img']); ?>">
								<span><?php echo($article['title']); ?></span>
							</a>
						</li>
					<?php } ?>
					<div style="clear:both"></div>
				</ul>
			</div>
		</div>

		<?php
			$video_tutorial = array(
				'overview' => array(
					'video' => 'http://player.vimeo.com/video/66113243',
					'title' => __('MyBookTable Overview', 'mybooktable'),
					'desc' => __('This video is a general introduction to MyBookTable.', 'mybooktable')
				),
				'buy_buttons' => array(
					'video' => 'http://player.vimeo.com/video/68790296',
					'title' => __('How to Add Buy Buttons', 'mybooktable'),
					'desc' => __('This video shows you how to add buy buttons to your books.', 'mybooktable')
				),
				'books_in_series' => array(
					'video' => 'http://player.vimeo.com/video/66110874',
					'title' => __('How to Put Books in a Series', 'mybooktable'),
					'desc' => __('This video shows you how to add books into a series.', 'mybooktable')
				),
				'amazon_affiliates' => array(
					'video' => 'http://player.vimeo.com/video/69188658',
					'title' => __('How to Setup an Amazon Affiliate Account With MyBookTable', 'mybooktable'),
					'desc' => __('This video walks you through setting up an Amazon Affiliate account and how to take your affiliate code and insert it into your MyBookTable plugin.', 'mybooktable')
				),
				'book_blurbs' => array(
					'video' => 'http://www.youtube.com/embed/LABESfhThhY',
					'title' => __('Effective Book Blurb Strategies', 'mybooktable'),
					'desc' => __('This video shows you how to write book blurbs for your books.', 'mybooktable')
				),
			);
		?>

		<?php if(isset($_GET['mbt_video_tutorial'])) { echo('<input type="hidden" id="mbt_selected_tutorial_video" value="'.$_GET['mbt_video_tutorial'].'">'); } ?>
		<div class="mbt_help_box mbt_video_tutorials">
			<div class="mbt_help_box_title"><?php _e('Tutorial Videos', 'mybooktable'); ?></div>
			<div class="mbt_video_selector">
				<?php foreach ($video_tutorial as $id => $tutorial) { ?>
					<a target="_blank" href="<?php echo($tutorial['video']); ?>" data-video-id="mbt_video_<?php echo($id); ?>" data-mbt-track-event="help_page_video_tutorial_click_<?php echo($id); ?>"><?php echo($tutorial['title']); ?></a>
				<?php } ?>
			</div>
			<div class="mbt_video_display">
				<?php foreach ($video_tutorial as $id => $tutorial) { ?>
					<div class="mbt_video" id="mbt_video_<?php echo($id); ?>">
						<iframe src="<?php echo($tutorial['video']); ?>" frameborder="0" allowfullscreen></iframe>
					</div>
				<?php } ?>
			</div>
			<div style="clear:both"></div>
		</div>

		<?php
			$wordpress_articles = array(
				'draw_readers' => array(
					'link' => 'http://www.authormedia.com/10-elements-proven-to-draw-readers-to-your-novels-website/',
					'img' => plugins_url('images/help/draw-readers.jpg', dirname(__FILE__)),
					'title' => __('10 Ways Proven to Draw Readers to Your Novel\'s Website', 'mybooktable')
				),
				'upload_file' => array(
					'link' => 'http://www.authormedia.com/how-to-upload-a-file-to-your-wordpress-site/',
					'img' => plugins_url('images/help/upload-file.jpg', dirname(__FILE__)),
					'title' => __('How to Upload a File to Your WordPress Site', 'mybooktable')
				),
				'create_pdf' => array(
					'link' => 'http://www.authormedia.com/how-to-create-a-pdf/',
					'img' => plugins_url('images/help/create-pdf.jpg', dirname(__FILE__)),
					'title' => __('How to Create a PDF', 'mybooktable')
				),
				'add_hyperlink' => array(
					'link' => 'http://www.authormedia.com/how-to-add-a-hyperlink-to-wordpress/',
					'img' => plugins_url('images/help/add-link.jpg', dirname(__FILE__)),
					'title' => __('How to Add a Hyperlink to WordPress', 'mybooktable')
				),
				'hotkeys_cheat_sheet' => array(
					'link' => 'http://www.authormedia.com/the-wordpress-hotkey-cheat-sheet-every-author-needs/',
					'img' => plugins_url('images/help/hotkeys.jpg', dirname(__FILE__)),
					'title' => __('The WordPress Hotkey Cheat Sheet Every Author Needs', 'mybooktable')
				),
				'write_posts' => array(
					'link' => 'http://www.authormedia.com/how-to-create-or-edit-posts-in-wordpress/',
					'img' => plugins_url('images/help/write-posts.jpg', dirname(__FILE__)),
					'title' => __('How to Create or Edit Posts in WordPress', 'mybooktable')
				),
				'protect_from_hackers' => array(
					'link' => 'http://www.authormedia.com/how-to-keep-your-wordpress-site-secure-from-hackers/',
					'img' => plugins_url('images/help/hackers.jpg', dirname(__FILE__)),
					'title' => __('How to Keep Your WordPress Site Secure From Hackers', 'mybooktable')
				),
				'zen_mode' => array(
					'link' => 'http://www.authormedia.com/how-to-find-zen-mode-in-wordpress/',
					'img' => plugins_url('images/help/zen.jpg', dirname(__FILE__)),
					'title' => __('How To Find Zen Mode in WordPress', 'mybooktable')
				),
				'book_marketing_ideas' => array(
					'link' => 'http://www.authormedia.com/89-book-marketing-ideas-that-will-change-your-life/',
					'img' => plugins_url('images/help/ideas.jpg', dirname(__FILE__)),
					'title' => __('89+ Book Marketing Ideas That Will Change Your Life', 'mybooktable')
				),
				'standard_nonfiction' => array(
					'link' => 'http://www.authormedia.com/standard-pages-for-a-non-fiction-website/',
					'img' => plugins_url('images/help/standard-nonfiction.jpg', dirname(__FILE__)),
					'title' => __('Standard Pages for A Non-Fiction Website', 'mybooktable')
				),
				'standard_fiction' => array(
					'link' => 'http://www.authormedia.com/standard-pages-for-a-fiction-website/',
					'img' => plugins_url('images/help/standard-fiction.jpg', dirname(__FILE__)),
					'title' => __('Standard Pages for A Fiction Website', 'mybooktable')
				),
				'what_readers_want' => array(
					'link' => 'http://www.authormedia.com/what-readers-want-from-your-author-website/',
					'img' => plugins_url('images/help/what-readers-want.jpg', dirname(__FILE__)),
					'title' => __('6 Things Readers Want from Your Author Website', 'mybooktable')
				),
			);
		?>

		<div class="mbt_help_box">
			<div class="mbt_help_box_title"><?php _e('General WordPress Guides &amp; Tutorials', 'mybooktable'); ?></div>
			<div class="mbt_help_box_content">
				<ul class="mbt_articles">
					<?php foreach ($wordpress_articles as $id => $article) { ?>
						<li>
							<a href="<?php echo($article['link']); ?>" target="_blank" data-mbt-track-event="help_page_wordpress_article_click_<?php echo($id); ?>">
								<img src="<?php echo($article['img']); ?>">
								<span><?php echo($article['title']); ?></span>
							</a>
						</li>
					<?php } ?>
					<div style="clear:both"></div>
				</ul>
			</div>
		</div>

		<?php do_action("mbt_render_help_page", 'mybooktable'); ?>

		<br>
	</div>

<?php
}

add_filter('wp101_get_custom_help_topics', 'mbt_add_wp101_help');
function mbt_add_wp101_help($videos) {
	$videos["mbt-overview"] = array("title" => "MyBookTable Overview", "content" => '<iframe width="640" height="360" src="http://player.vimeo.com/video/66113243" frameborder="0" allowfullscreen></iframe>');
	$videos["mbt-buybuttons"] = array("title" => "MyBookTable Buy Buttons", "content" => '<iframe width="640" height="360" src="http://player.vimeo.com/video/68790296" frameborder="0" allowfullscreen></iframe>');
	$videos["mbt-booksinseries"] = array("title" => "How to Put Books in a Series", "content" => '<iframe width="640" height="360" src="http://player.vimeo.com/video/66110874" frameborder="0" allowfullscreen></iframe>');
	$videos["mbt-amazonaffiliates"] = array("title" => "MyBookTable Amazon Affiliate Accounts", "content" => '<iframe width="640" height="360" src="http://player.vimeo.com/video/69188658" frameborder="0" allowfullscreen></iframe>');
	$videos["mbt-bookblurbs"] = array("title" => "MyBookTable Book Blurbs", "content" => '<iframe width="640" height="360" src="http://www.youtube.com/embed/LABESfhThhY" frameborder="0" allowfullscreen></iframe>');
	return $videos;
}

function mbt_render_dashboard() {
	if(!empty($_GET['subpage']) and $_GET['subpage'] == 'mbt_founders_page') { return mbt_render_founders_page(); }
	if(!empty($_GET['subpage']) and $_GET['subpage'] == 'mbt_get_upgrade_page') { return mbt_render_get_upgrade_page(); }
?>

	<div class="wrap mbt-dashboard">
		<div id="icon-index" class="icon32"><br></div><h2><?php _e('MyBookTable', 'mybooktable'); ?></h2>
		<?php if(!mbt_get_ab_testing_status('dashboard_promotions_box_position')) { ?>
			<table><tbody>
				<tr>
					<td class="dashboard-contents-left">
		<?php } ?>
		<div class="welcome-video-container">
			<div class="welcome-video welcome-panel">
				<iframe width="640" height="360" src="http://player.vimeo.com/video/66113243" frameborder="0" allowfullscreen></iframe><br>
				<a href="<?php echo(admin_url('admin.php?page=mbt_help')); ?>" data-mbt-track-event-override="dashboard_more_tutorial_videos_click"><?php _e('More Tutorial Videos', 'mybooktable'); ?></a>
			</div>
		</div>

		<div class="buttons-container">
			<a href="<?php echo(admin_url('post-new.php?post_type=mbt_book')); ?>" class="add-new-book" data-mbt-track-event-override="dashboard_add_new_book_click"><?php _e('Add New Book', 'mybooktable'); ?></a>
		</div>

		<div class="welcome-panel">
			<div class="welcome-panel-content">
				<h3><?php _e('Welcome to MyBookTable!', 'mybooktable'); ?></h3>
				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column">
						<h4><?php _e('First Steps', 'mybooktable'); ?></h4>
						<ul>
							<?php if(!mbt_get_setting('installed_examples')) { ?>
								<li><a href="<?php echo(admin_url('edit.php?post_type=mbt_book&mbt_install_examples=1')); ?>" class="welcome-icon welcome-view-site" data-mbt-track-event-override="dashboard_install_examples_click"><?php _e('Look at some example Books', 'mybooktable'); ?></a></li>
							<?php } ?>
							<li><a href="<?php echo(admin_url('post-new.php?post_type=mbt_book')); ?>" class="welcome-icon welcome-add-page" data-mbt-track-event-override="dashboard_create_first_book_click"><?php _e('Create your first book', 'mybooktable'); ?></a></li>
							<li><a href="<?php echo(admin_url('admin.php?page=mbt_import')); ?>" class="welcome-icon welcome-widgets-menus" data-mbt-track-event-override="dashboard_import_books_click"><?php _e('Import Books', 'mybooktable'); ?></a></li>
							<li><a href="<?php echo(mbt_get_booktable_url()); ?>" class="welcome-icon welcome-view-site" data-mbt-track-event-override="dashboard_view_book_table_click"><?php _e('View your Book Table', 'mybooktable'); ?></a></li>
						</ul>
					</div>
					<div class="welcome-panel-column">
						<h4><?php _e('Actions', 'mybooktable'); ?></h4>
						<ul>
							<li><div class="welcome-icon welcome-widgets-menus"><?php _e('Manage', 'mybooktable'); ?> <a href="<?php echo(admin_url('edit.php?post_type=mbt_book')); ?>" data-mbt-track-event-override="dashboard_manage_books_click"><?php _e('Books', 'mybooktable'); ?></a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus"><?php _e('Manage', 'mybooktable'); ?> <a href="<?php echo(admin_url('edit-tags.php?taxonomy=mbt_author')); ?>" data-mbt-track-event-override="dashboard_manage_authors_click"><?php _e('Authors', 'mybooktable'); ?></a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus"><?php _e('Manage', 'mybooktable'); ?> <a href="<?php echo(admin_url('edit-tags.php?taxonomy=mbt_genre')); ?>" data-mbt-track-event-override="dashboard_manage_genres_click"><?php _e('Genres', 'mybooktable'); ?></a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus"><?php _e('Manage', 'mybooktable'); ?> <a href="<?php echo(admin_url('edit-tags.php?taxonomy=mbt_series')); ?>" data-mbt-track-event-override="dashboard_manage_series_click"><?php _e('Series', 'mybooktable'); ?></a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus"><?php _e('Manage', 'mybooktable'); ?> <a href="<?php echo(admin_url('edit-tags.php?taxonomy=mbt_tag')); ?>" data-mbt-track-event-override="dashboard_manage_tags_click"><?php _e('Tags', 'mybooktable'); ?></a></div></li>
							<li><div class="welcome-icon welcome-widgets-menus"><?php _e('Manage', 'mybooktable'); ?> <a href="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>" data-mbt-track-event-override="dashboard_manage_settings_click"><?php _e('Settings', 'mybooktable'); ?></a></div></li>
						</ul>
					</div>
					<div class="welcome-panel-column welcome-panel-last">
						<h4><?php _e('Resources', 'mybooktable'); ?></h4>
						<ul>
							<li><a href="<?php echo(admin_url('admin.php?page=mbt_help')); ?>" class="welcome-icon welcome-learn-more" data-mbt-track-event-override="dashboard_get_help_using_mybooktable_click"><?php _e('Get help using MyBookTable', 'mybooktable'); ?></a></li>
							<li><a href="https://github.com/authormedia/mybooktable/wiki" class="welcome-icon welcome-learn-more" target="_blank" data-mbt-track-event="dashboard_developer_documentation_click"><?php _e('Developer Documentation', 'mybooktable'); ?></a></li>
							<li><a href="http://authormedia.us1.list-manage.com/subscribe?u=b7358f48fe541fe61acdf747b&amp;id=6b5a675fcf" class="welcome-icon welcome-write-blog" target="_blank" data-mbt-track-event="dashboard_sign_up_for_tips_click"><?php _e('Sign Up for Book Marketing Tips from Author Media', 'mybooktable'); ?></a></li>
							<li><a href="<?php echo(admin_url('admin.php?page=mbt_dashboard&subpage=mbt_founders_page')); ?>" class="welcome-icon welcome-write-blog" data-mbt-track-event-override="dashboard_plugin_founders_click"><?php _e('Plugin Founders', 'mybooktable'); ?></a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<div style="clear:both"></div>
		<?php if(mbt_get_ab_testing_status('dashboard_promotions_box_position')) { ?>
			<div class="welcome-panel welcome-panel-promotions">
				<div class="welcome-panel-content">
					<h3><?php _e('Promotions', 'mybooktable'); ?></h3>
					<div class="welcome-panel-column-container">
						<div class="welcome-panel-column">
							<a href="http://authormedia.us1.list-manage.com/subscribe?u=b7358f48fe541fe61acdf747b&amp;id=6b5a675fcf" target="_blank" data-mbt-track-event="dashboard_promotion_click_amazing_websites"><img align="none" src="<?php echo(plugins_url('images/promotions/amazing_websites.jpg', dirname(__FILE__))); ?>" style="width: 300px; height: 300px;"></a>
						</div>
						<div class="welcome-panel-column">
							<a href="http://www.authormedia.com/store/write-novel-month-mp3-ebook/" target="_blank" data-mbt-track-event="dashboard_promotion_click_novel_month"><img align="none" src="<?php echo(plugins_url('images/promotions/novel_month.jpg', dirname(__FILE__))); ?>" style="width: 300px; height: 300px;"></a>
						</div>
						<div class="welcome-panel-column">
							<a href="http://www.authormedia.com/store/7-tax-saving-tips-irs-doesnt-want-authors-know/" target="_blank" data-mbt-track-event="dashboard_promotion_click_tax_strategies_authors"><img align="none" src="<?php echo(plugins_url('images/promotions/tax_strategies_authors.jpg', dirname(__FILE__))); ?>" style="width: 300px; height: 300px;"></a>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>

		<div style="clear:both"></div>
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
					<h1 class="mybooktable-version"><?php printf(__('You are currently using <span class="current-version">MyBookTable %s</span>', 'mybooktable'), MBT_VERSION); ?></h1>
					<?php if(mbt_get_upgrade() == 'mybooktable-dev2' and mbt_get_upgrade_plugin_exists()) { ?>
						<h1 class="upgrade-version"><?php printf(__('with the <span class="current-version">Developer Upgrade %s</span>', 'mybooktable'), MBTDEV2_VERSION); ?></h1>
						<h2 class="thank-you"><?php _e('Thank you for your support!', 'mybooktable'); ?></h2>
					<?php } else if(mbt_get_upgrade() == 'mybooktable-pro2' and mbt_get_upgrade_plugin_exists()) { ?>
						<h1 class="upgrade-version"><?php printf(__('with the <span class="current-version">Professional Upgrade %s</span>', 'mybooktable'), MBTPRO2_VERSION); ?></h1>
						<h2 class="thank-you"><?php _e('Thank you for your support!', 'mybooktable'); ?></h2>
					<?php } else if(mbt_get_upgrade() == 'mybooktable-dev' and mbt_get_upgrade_plugin_exists()) { ?>
						<h1 class="upgrade-version"><?php printf(__('with the <span class="current-version">Developer Upgrade %s</span>', 'mybooktable'), MBTDEV_VERSION); ?></h1>
						<h2 class="thank-you"><?php _e('Thank you for your support!', 'mybooktable'); ?></h2>
					<?php } else if(mbt_get_upgrade() == 'mybooktable-pro' and mbt_get_upgrade_plugin_exists()) { ?>
						<h1 class="upgrade-version"><?php printf(__('with the <span class="current-version">Professional Upgrade %s</span>', 'mybooktable'), MBTPRO_VERSION); ?></h1>
						<h2 class="thank-you"><?php _e('Thank you for your support!', 'mybooktable'); ?></h2>
					<?php } else { ?>
						<h2 class="upgrade-title"><?php _e('Upgrade your MyBookTable and get:', 'mybooktable'); ?></h2>
						<ul class="upgrade-list">
							<li><?php _e('Premium Support', 'mybooktable'); ?></li>
							<li><?php _e('Amazon Affiliate Integration', 'mybooktable'); ?></li>
							<li><?php _e('Barnes &amp; Noble Affiliate Integration', 'mybooktable'); ?></li>
							<li><?php _e('Universal Buy Button', 'mybooktable'); ?></li>
							<li><a href="http://mybooktable.com" target="_blank" data-mbt-track-event="dashboard_upsell_box_click"><?php _e('And much much more', 'mybooktable'); ?></a></li>
						</ul>
					<?php } ?>
				</div>
			</div>
		</div>

		<?php if(!mbt_get_ab_testing_status('dashboard_promotions_box_position')) { ?>
				</td>
				<td class="welcome-panel welcome-panel-promotions-right">
					<div>
						<div class="welcome-panel-content">
							<h3><?php _e('Promotions', 'mybooktable'); ?></h3>
							<div class="welcome-panel-column-container">
								<div class="welcome-panel-column">
									<a href="http://authormedia.us1.list-manage.com/subscribe?u=b7358f48fe541fe61acdf747b&amp;id=6b5a675fcf" target="_blank" data-mbt-track-event="dashboard_promotion_click_amazing_websites"><img src="<?php echo(plugins_url('images/promotions/amazing_websites.jpg', dirname(__FILE__))); ?>"></a>
								</div>
								<div class="welcome-panel-column">
									<a href="http://www.authormedia.com/store/write-novel-month-mp3-ebook/" target="_blank" data-mbt-track-event="dashboard_promotion_click_novel_month"><img src="<?php echo(plugins_url('images/promotions/novel_month.jpg', dirname(__FILE__))); ?>"></a>
								</div>
								<div class="welcome-panel-column">
									<a href="http://www.authormedia.com/store/7-tax-saving-tips-irs-doesnt-want-authors-know/" target="_blank" data-mbt-track-event="dashboard_promotion_click_tax_strategies_authors"><img src="<?php echo(plugins_url('images/promotions/tax_strategies_authors.jpg', dirname(__FILE__))); ?>"></a>
								</div>
							</div>
						</div>
					</div>
				</td>
			</tr>
		</tbody></table>
		<div style="clear:both"></div>
		<?php } ?>
	</div>

<?php
}

function mbt_render_get_upgrade_page() {
?>
	<div class="wrap mbt_settings">
		<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('Get Upgrade', 'mybooktable'); ?></h2>
		<?php
			function mbt_get_upgrade_check_is_plugin_inactivate($slug) {
				$plugin = $slug.DIRECTORY_SEPARATOR.$slug.'.php';
				if(!is_wp_error(activate_plugin($plugin))) {
					echo('<p>'.__('Plugin successfully activated.', 'mybooktable').'</p>');
					return true;
				}

				return false;
			}

			function mbt_get_upgrade_get_plugin_url($slug) {
				global $wp_version;

				$api_key = mbt_get_setting('api_key');
				if(!empty($api_key)) {
					$to_send = array(
						'action'  => 'basic_check',
						'version' => 'none',
						'api-key' => $api_key,
						'site'    => get_bloginfo('url')
					);

					$options = array(
						'timeout' => 3,
						'body' => $to_send,
						'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url')
					);

					$raw_response = wp_remote_post('http://www.authormedia.com/plugins/'.$slug.'/update-check', $options);
					if(!is_wp_error($raw_response) and wp_remote_retrieve_response_code($raw_response) == 200) {
						$response = maybe_unserialize(wp_remote_retrieve_body($raw_response));
						if(is_array($response) and !empty($response['package'])) {
							return $response['package'];
						}
					}
				}

				return '';
			}

			function mbt_get_upgrade_do_plugin_install($name, $slug, $url) {
				if(empty($url)) { echo('<p>'.__('An error occurred while trying to retrieve the plugin from the server. Please check your API Key.', 'mybooktable').'</p>'); return; }
				if(!current_user_can('install_plugins')) { echo('<p>'.__('Sorry, but you do not have the correct permissions to install plugins. Contact the administrator of this site for help on getting the plugin installed.', 'mybooktable').'</p>'); return; }

				$nonce_url = wp_nonce_url('admin.php?page=mbt_dashboard', 'mbt-install-upgrade');
				$output = mbt_get_wp_filesystem($nonce_url);
				if(!empty($output)) { echo($output); return; }

				$plugin = array();
				$plugin['name']   = $name;
				$plugin['slug']   = $slug;
				$plugin['source'] = $url;

				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

				$args = array(
					'type'   => 'web',
					'title'  => sprintf(__('Installing Plugin: %s', 'mybooktable'), $plugin['name']),
					'nonce'  => 'install-plugin_' . $plugin['slug'],
					'plugin' => $plugin,
				);

				add_filter('install_plugin_complete_actions', '__return_false', 100);
				$upgrader = new Plugin_Upgrader(new Plugin_Installer_Skin($args));
				$upgrader->install($plugin['source']);
				wp_cache_flush();
				remove_filter('install_plugin_complete_actions', '__return_false', 100);

				$plugin_info = $upgrader->plugin_info();
				$activate    = activate_plugin($plugin_info);
				if(is_wp_error($activate)) { echo('<div id="message" class="error"><p>'.$activate->get_error_message().'</p></div>'); }
			}

			$slug = mbt_get_upgrade();
			if(empty($slug) or mbt_get_upgrade_plugin_exists()) {
				echo('<p>'.__('You have no Upgrades available to download at this time.', 'mybooktable').'</p>');
			} else {
				if(!mbt_get_upgrade_check_is_plugin_inactivate($slug)) {
					$url = mbt_get_upgrade_get_plugin_url($slug);
					if($slug == 'mybooktable-dev2') { $name = 'MyBookTable Developer Upgrade 2.0'; }
					if($slug == 'mybooktable-pro2') { $name = 'MyBookTable Professional Upgrade 2.0'; }
					if($slug == 'mybooktable-dev')  { $name = 'MyBookTable Developer Upgrade'; }
					if($slug == 'mybooktable-pro')  { $name = 'MyBookTable Professional Upgrade'; }
					mbt_get_upgrade_do_plugin_install($name, $slug, $url);
				}
			}
		?>
		<a class="button button-primary" href="<?php echo(admin_url('admin.php?page=mbt_dashboard')); ?>"><?php _e('Back to Dashboard', 'mybooktable'); ?></a>
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

function mbt_render_import_page() {
	mbt_track_event('view_import_page');
	$importers = mbt_get_importers();

	if(!empty($_GET['mbt_import_type'])) {
		$import_type = $_GET['mbt_import_type'];
		if(!empty($importers[$import_type])) {
			return call_user_func_array($importers[$import_type]['callback'], array());
		}
		return mbt_render_founders_page();
	}
?>
	<div class="wrap mbt_import_page">
		<h2>Import Books</h2>
		<p>If you have books in another system, MyBookTable can import those into this site. To get started, choose a system to import from below:</p>
		<table class="widefat importers">
			<tbody>
				<?php
					$alternate = true;
					foreach($importers as $slug => $importer) {
						echo('<tr'.($alternate ? ' class="alternate"' : '').'>');
						if(empty($importer['disabled'])) {
							echo('<td class="import-system row-title"><a href="'.admin_url('admin.php?page=mbt_import&mbt_import_type='.$slug).'">'.$importer['name'].'</a></td>');
						} else {
							echo('<td class="import-system row-title disabled">'.$importer['name'].'</td>');
						}
						echo('<td class="desc">'.$importer['desc'].((!empty($importer['disabled']) and is_string($importer['disabled'])) ? ' ('.$importer['disabled'].')' : '').'</td>');
						echo('</tr>');
						$alternate = !$alternate;
					}
				?>
			</tbody>
		</table>
	</div>
<?php
}
