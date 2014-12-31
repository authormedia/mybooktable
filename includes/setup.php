<?php

/*---------------------------------------------------------*/
/* Check for Upgrades                                      */
/*---------------------------------------------------------*/

function mbt_upgrade_check()
{
	$version = mbt_get_setting("version");

	if(version_compare($version, "1.1.0") < 0) { mbt_upgrade_1_1_0(); }
	if(version_compare($version, "1.1.3") < 0) { mbt_upgrade_1_1_3(); }
	if(version_compare($version, "1.1.4") < 0) { mbt_upgrade_1_1_4(); }
	if(version_compare($version, "1.2.7") < 0) { mbt_upgrade_1_2_7(); }
	if(version_compare($version, "1.3.1") < 0) { mbt_upgrade_1_3_1(); }
	if(version_compare($version, "1.3.2") < 0) { mbt_upgrade_1_3_2(); }

	if($version !== MBT_VERSION) { mbt_update_setting("version", MBT_VERSION); }
}

function mbt_upgrade_1_1_0() {
	if(mbt_get_setting('compatibility_mode') !== false) {
		mbt_update_setting('compatibility_mode', true);
	}
}

function mbt_upgrade_1_1_3() {
	global $wpdb;
	$books = $wpdb->get_col('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = "mbt_book"');
	if(!empty($books)) {
		foreach($books as $book_id) {
			$image_id = get_post_meta($book_id, '_thumbnail_id', true);
			$mbt_book_image_id = get_post_meta($book_id, 'mbt_book_image_id', true);
			if(empty($mbt_book_image_id) && !empty($image_id)) { update_post_meta($book_id, 'mbt_book_image_id', $image_id); }
		}
	}
}

function mbt_upgrade_1_1_4() {
	global $wpdb;
	$books = $wpdb->get_col('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = "mbt_book"');
	if(!empty($books)) {
		foreach($books as $book_id) {
			delete_post_meta($book_id, '_thumbnail_id');

			$buybuttons = get_post_meta($book_id, 'mbt_buybuttons', true);
			if(is_array($buybuttons) and !empty($buybuttons)) {
				for($i = 0; $i < count($buybuttons); $i++)
				{
					if($buybuttons[$i]['type']) {
						$buybuttons[$i]['store'] = $buybuttons[$i]['type'];
						unset($buybuttons[$i]['type']);
					}
				}
			}
			update_post_meta($book_id, 'mbt_buybuttons', $buybuttons);
		}
	}
}

function mbt_upgrade_1_2_7() {
	if(mbt_get_setting('enable_default_affiliates') !== false) {
		mbt_update_setting('enable_default_affiliates', true);
	}
}

function mbt_upgrade_1_3_1() {
	mbt_update_setting('help_page_email_subscribe_popup', 'show');
	$func = create_function("", "wp_insert_term('".__("Recommended Books")."', 'mbt_tag', array('slug' => 'recommended'));");
	add_action('init', $func, 20);
	mbt_update_setting('product_name', __("Books"));
	mbt_update_setting('product_slug', _x('books', 'URL slug', 'mybooktable'));
}

function mbt_upgrade_1_3_2() {
	$func = create_function("", "flush_rewrite_rules();");
	add_action('init', $func, 999);
}




/*---------------------------------------------------------*/
/* Installation Functions                                  */
/*---------------------------------------------------------*/

function mbt_install() {
	mbt_install_pages();
	mbt_install_examples();
}

function mbt_install_pages() {
	if(mbt_get_setting('booktable_page') <= 0 or !get_page(mbt_get_setting('booktable_page'))) {
		$post_id = wp_insert_post(array(
			'post_title' => __('Book Table', 'mybooktable'),
			'post_content' => '',
			'post_status' => 'publish',
			'post_type' => 'page'
		));
		mbt_update_setting("booktable_page", $post_id);
	}
}

function mbt_install_examples() {
	if(!mbt_get_setting('installed_examples')) {
		include("examples.php");
		mbt_update_setting('installed_examples', true);
	}
}



/*---------------------------------------------------------*/
/* Admin notices                                           */
/*---------------------------------------------------------*/

function mbt_admin_notices_init() {
	add_action('admin_init', 'mbt_add_admin_notices', 20);
}
add_action('mbt_init', 'mbt_admin_notices_init');

function mbt_add_admin_notices() {
	if(!mbt_get_setting('installed')) {
		if(isset($_GET['install_mbt'])) {
			mbt_install();
			mbt_update_setting('installed', 'check_api_key');
		} elseif(isset($_GET['skip_install_mbt']) || mbt_get_setting('booktable_page') != 0) {
			mbt_update_setting('installed', 'check_api_key');
		} else {
			add_action('admin_notices', 'mbt_admin_install_notice');
		}
	}
	if(mbt_get_setting('installed') == 'check_api_key') {
		if(!mbt_get_setting('api_key') and (defined('MBTPRO_VERSION') or defined('MBTDEV_VERSION'))) {
			add_action('admin_notices', 'mbt_admin_setup_api_key_notice');
		} else {
			mbt_update_setting('installed', 'setup_default_affiliates');
		}
	}
	if(mbt_get_setting('installed') == 'setup_default_affiliates') {
		if(!mbt_get_setting('enable_default_affiliates') and !mbt_get_setting('pro_active') and !mbt_get_setting('dev_active') and !isset($_GET['mbt_setup_default_affiliates'])) {
			add_action('admin_notices', 'mbt_admin_setup_default_affiliates_notice');
		} else {
			mbt_update_setting('installed', 'post_install');
		}
	}
	if(mbt_get_setting('installed') == 'post_install') {
		if(isset($_GET['finish_install_mbt'])) {
			do_action('mbt_installed');
			mbt_update_setting('installed', 'done');
		} else {
			add_action('admin_notices', 'mbt_admin_installed_notice');
		}
	}
	if(mbt_get_setting('installed') == 'done') {
		if(!mbt_get_setting('api_key') and (defined('MBTPRO_VERSION') or defined('MBTDEV_VERSION'))) {
			add_action('admin_notices', 'mbt_admin_setup_api_key_notice');
		}
	}
	if(mbt_get_setting('installed') == 'done') {
		if((mbt_get_setting('dev_active') and !defined('MBTDEV_VERSION')) or ((!mbt_get_setting('dev_active') and mbt_get_setting('pro_active')) and !defined('MBTPRO_VERSION'))) {
			add_action('admin_notices', 'mbt_admin_download_addon_notice');
		}
	}
	if(mbt_get_setting('installed') == 'done') {
		if(mbt_get_setting('api_key_status') === -12) {
			add_action('admin_notices', 'mbt_admin_api_key_expired_notice');
		}
	}
	if(mbt_get_setting('help_page_email_subscribe_popup') == 'show') {
		if(isset($_POST['mbt_email_subscribe'])) {
			mbt_update_setting('help_page_email_subscribe_popup', 'done');

			if($_POST['mbt_email_address']) {
				wp_remote_post('http://AuthorMedia.us1.list-manage1.com/subscribe/post', array(
					'method' => 'POST',
					'body' => array(
						'u' => 'b7358f48fe541fe61acdf747b',
						'id' => '6b5a675fcf',
						'MERGE0' => $_POST['mbt_email_address'],
						'MERGE1' => '',
						'MERGE3' => '',
						'group[3045][64]' => 'on',
						'b_b7358f48fe541fe61acdf747b_6b5a675fcf' => ''
				)));
				add_action('admin_notices', 'mbt_admin_email_subscribe_thankyou_notice');
			}
		} else {
			add_action('admin_notices', 'mbt_admin_email_subscribe_notice');
		}
	}

	if(isset($_GET['mbt_install_examples'])) {
		mbt_install_examples();
	}

	if(isset($_GET['mbt_install_pages'])) {
		mbt_install_pages();
	}
}

function mbt_admin_install_notice() {
	?>
	<div class="mbt-admin-notice">
		<h4><?php _e('<strong>Welcome to MyBookTable</strong> &#8211; You\'re almost ready to start promoting your books :)', 'mybooktable'); ?></h4>
		<a class="notice-button primary" href="<?php echo(admin_url('admin.php?page=mbt_settings&install_mbt=1')); ?>"><?php _e('Install MyBookTable Pages', 'mybooktable'); ?></a>
		<a class="notice-button secondary" href="<?php echo(admin_url('admin.php?page=mbt_settings&skip_install_mbt=1')); ?>"><?php _e('Skip setup', 'mybooktable'); ?></a>
	</div>
	<?php
}

function mbt_admin_installed_notice() {
	?>
	<div id="message" class="mbt-admin-notice">
		<h4><?php _e('<strong>MyBookTable has been installed</strong> &#8211; You\'re ready to start promoting your books :)', 'mybooktable'); ?></h4>
		<a class="notice-button primary" href="<?php echo(admin_url('admin.php?page=mbt_help&finish_install_mbt=1')); ?>"><?php _e('Show Me How', 'mybooktable'); ?></a>
		<a class="notice-button secondary" href="<?php echo(admin_url('admin.php?page=mbt_settings&finish_install_mbt=1')); ?>"><?php _e('Thanks, I Got This', 'mybooktable'); ?></a>
	</div>
	<?php
}

function mbt_admin_setup_api_key_notice() {
	?>
	<div id="message" class="mbt-admin-notice">
		<h4><?php _e('<strong>Setup your API Key</strong> &#8211; MyBookTable needs your API key to enable enhanced features', 'mybooktable'); ?></h4>
		<a class="notice-button primary" href="<?php echo(admin_url('admin.php?page=mbt_settings&setup_api_key=1')); ?>"><?php _e('Go To Settings', 'mybooktable'); ?></a>
	</div>
	<?php
}

function mbt_admin_setup_default_affiliates_notice() {
	?>
	<div id="message" class="mbt-admin-notice">
		<h4><?php _e('<strong>Setup your Amazon and Barnes &amp; Noble Buttons</strong> &#8211; MyBookTable needs your input to enable these features', 'mybooktable'); ?></h4>
		<a class="notice-button primary" href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_setup_default_affiliates=1')); ?>"><?php _e('Go To Settings', 'mybooktable'); ?></a>
	</div>
	<?php
}

function mbt_admin_download_addon_notice() {
	$name = (mbt_get_setting('dev_active') and !defined('MBTDEV_VERSION')) ? __("Developer", 'mybooktable') : ((mbt_get_setting('pro_active') and !defined('MBTPRO_VERSION')) ? __("Professional", 'mybooktable') : "");
	?>
	<div id="message" class="mbt-admin-notice">
		<h4><?php _e('<strong>Download your Add-on</strong> &#8211; Download the MyBookTable <?php echo($name); ?> Add-on to activate your advanced features!', 'mybooktable'); ?></h4>
		<a class="notice-button primary" href="https://gumroad.com/library/" target="_blank"><?php _e('Download', 'mybooktable'); ?></a>
	</div>
	<?php
}

function mbt_admin_api_key_expired_notice() {
	?>
	<div id="message" class="mbt-admin-notice">
		<h4><?php _e('<strong>Uh Oh!</strong> &#8211; Looks like your MyBookTable API Key has expired.', 'mybooktable'); ?></h4>
		<a class="notice-button primary" href="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>"><?php _e('Go To Settings', 'mybooktable'); ?></a>
		<a class="notice-button primary" target="_blank" href="http://www.authormedia.com/mybooktable/"><?php _e('Renew your License', 'mybooktable'); ?></a>
	</div>
	<?php
}

function mbt_admin_email_subscribe_notice() {
	$current_user = wp_get_current_user();
	$email = $current_user->user_email;
	?>
	<div class="mbt-admin-notice mbt-email-subscribe-message">
		<h4><?php _e('<strong>Want Book Marketing Tips?</strong> &#8211; Subscribe to the Author Media newsletter!', 'mybooktable'); ?></h4>
		<form action="" method="POST">
			<input type="hidden" name="mbt_email_subscribe" value="1">
			<input type="email" name="mbt_email_address" id="mbt_email_address" autocapitalize="off" autocorrect="off" size="25" value="<?php echo($email); ?>" placeholder="you@example.com">
			<input type="Submit" class="notice-button primary" value="<?php _e('Subscribe', 'mybooktable'); ?>" onclick="if(!/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/.test(jQuery('#mbt_email_address').val())){jQuery('#mbt_email_address').focus().css('background', '#FFEBE8');return false;}">
			<input type="Submit" class="notice-button secondary" value="<?php _e('No Thanks', 'mybooktable'); ?>" onclick="jQuery('#mbt_email_address').val('');">
		</form>
	</div>
	<?php
}

function mbt_admin_email_subscribe_thankyou_notice() {
	?>
	<div class="mbt-admin-notice mbt-email-subscribe-message">
		<h4><strong><?php _e('Thank you for subscribing!</strong> &#8211; Please check your inbox for a confirmation letter.', 'mybooktable'); ?></h4>
	</div>
	<?php
}



/*---------------------------------------------------------*/
/* Uninstallation Functions                                */
/*---------------------------------------------------------*/

function mbt_uninstall() {
	//erase options
	delete_option('mbt_settings');

	//erase taxonomies
	mbt_erase_taxonomy('mbt_author');
	mbt_erase_taxonomy('mbt_series');
	mbt_erase_taxonomy('mbt_genre');
	mbt_erase_taxonomy('mbt_tag');

	//erase books
	global $wpdb;
	$wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'mbt_book'");

	//erase rewrites
	add_action('admin_init', 'flush_rewrite_rules');

	//erase plugin
	$active_plugins = get_option('active_plugins');
	$plugin = plugin_basename(dirname(dirname(__FILE__))."/mybooktable.php");
	unset($active_plugins[array_search($plugin, $active_plugins)]);
	update_option('active_plugins', $active_plugins);
}

function mbt_erase_taxonomy($name) {
	global $wpdb;
	$wpdb->query("DELETE term_rel.* FROM $wpdb->term_relationships AS term_rel INNER JOIN $wpdb->term_taxonomy AS term_tax WHERE term_rel.term_taxonomy_id = term_tax.term_taxonomy_id AND term_tax.taxonomy = '".$name."'");
	$wpdb->query("DELETE FROM $wpdb->term_taxonomy WHERE taxonomy = '".$name."'");
}