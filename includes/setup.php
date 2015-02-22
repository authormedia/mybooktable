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
	if(version_compare($version, "1.3.8") < 0) { mbt_upgrade_1_3_8(); }
	if(version_compare($version, "2.0.1") < 0) { mbt_upgrade_2_0_1(); }
	if(version_compare($version, "2.0.4") < 0) { mbt_upgrade_2_0_4(); }

	if($version !== MBT_VERSION) {
		mbt_track_event('plugin_updated', true);
		mbt_update_setting("version", MBT_VERSION);
	}
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

function mbt_upgrade_1_3_8() {
	mbt_update_setting('domc_notice_text', __('Disclosure of Material Connection: Some of the links in the page above are "affiliate links." This means if you click on the link and purchase the item, I will receive an affiliate commission. I am disclosing this in accordance with the Federal Trade Commission\'s <a href="http://www.access.gpo.gov/nara/cfr/waisidx_03/16cfr255_03.html" target="_blank">16 CFR, Part 255</a>: "Guides Concerning the Use of Endorsements and Testimonials in Advertising."', 'mybooktable'));
}

function mbt_upgrade_2_0_1() {
	mbt_verify_api_key();
}

function mbt_upgrade_2_0_4() {
	mbt_update_setting('show_find_bookstore_buybuttons_shadowbox', true);
}



/*---------------------------------------------------------*/
/* Rewrites Check                                          */
/*---------------------------------------------------------*/

function mbt_rewrites_check_init() {
	add_action('init', 'mbt_rewrites_check', 999);
}
add_action('mbt_init', 'mbt_rewrites_check_init');

function mbt_rewrites_check() {
	if(!mbt_check_rewrites()) { flush_rewrite_rules(); }
	if(!mbt_check_rewrites()) { add_action('admin_notices', 'mbt_rewrites_check_admin_notice'); }
}

function mbt_check_rewrites() {
	global $wp_rewrite;
	$rules = $wp_rewrite->wp_rewrite_rules();
	if(empty($rules) or !is_array($rules)) { return true; }

	$archive_correct = mbt_get_rewrite($rules, mbt_get_product_slug()) === 'index.php?post_type=mbt_book';
	$book_page_correct = mbt_get_rewrite($rules, mbt_get_product_slug().'/book') === 'index.php?mbt_book=$matches[1]&page=$matches[2]';
	$genres_correct = mbt_get_rewrite($rules, apply_filters('mbt_genre_rewrite_name', _x('genre', 'URL slug', 'mybooktable')).'/genre') === 'index.php?mbt_genre=$matches[1]';
	$authors_correct = mbt_get_rewrite($rules, apply_filters('mbt_author_rewrite_name', _x('authors', 'URL slug', 'mybooktable')).'/author') === 'index.php?mbt_author=$matches[1]';
	$series_correct = mbt_get_rewrite($rules, apply_filters('mbt_series_rewrite_name', _x('series', 'URL slug', 'mybooktable')).'/series') === 'index.php?mbt_series=$matches[1]';
	$tags_correct = mbt_get_rewrite($rules, apply_filters('mbt_tag_rewrite_name', mbt_get_product_slug()._x('tag', 'URL slug', 'mybooktable')).'/tag') === 'index.php?mbt_tag=$matches[1]';

	return $archive_correct and $book_page_correct and $genres_correct and $authors_correct and $series_correct and $tags_correct;
}

function mbt_get_rewrite($rules, $url) {
	foreach($rules as $match => $query) {
		if(preg_match("#^$match#", $url)) {
			return $query;
		}
	}
	return '';
}

function mbt_rewrites_check_admin_notice() {
	?>
	<div id="message" class="error">
		<p>
			<strong><?php _e('MyBookTable Rewrites Error', 'mybooktable'); ?></strong> &#8211;
			<?php printf(__('You have a plugin or theme that has post types or taxonomies that are conflicting with MyBookTable. MyBookTable pages will not display correctly.', 'mybooktable'), mbt_get_product_slug()); ?>
		</p>
	</div>
	<?php
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
		if(!mbt_get_setting('api_key') and mbt_get_upgrade_plugin_exists(false)) {
			add_action('admin_notices', 'mbt_admin_setup_api_key_notice');
		} else {
			mbt_update_setting('installed', 'setup_default_affiliates');
		}
	}
	if(mbt_get_setting('installed') == 'setup_default_affiliates') {
		if(!mbt_get_setting('enable_default_affiliates') and mbt_get_upgrade() === false and !isset($_GET['mbt_setup_default_affiliates'])) {
			add_action('admin_notices', 'mbt_admin_setup_default_affiliates_notice');
		} else {
			mbt_update_setting('installed', 'post_install');
		}
	}
	if(mbt_get_setting('installed') == 'post_install') {
		if(isset($_GET['mbt_finish_install'])) {
			do_action('mbt_installed');
			mbt_update_setting('installed', 'done');
		} else {
			add_action('admin_notices', 'mbt_admin_installed_notice');
		}
	}
	if(mbt_get_setting('installed') == 'done' or is_int(mbt_get_setting('installed'))) {
		if(!mbt_get_setting('api_key') and mbt_get_upgrade_plugin_exists(false)) {
			add_action('admin_notices', 'mbt_admin_setup_api_key_notice');
		} else if(mbt_get_upgrade() and !mbt_get_upgrade_plugin_exists()) {
			add_action('admin_notices', 'mbt_admin_enable_upgrade_notice');
		} else if(!mbt_get_setting('allow_tracking') and current_user_can('manage_options')) {
			add_action('admin_notices', 'mbt_admin_allow_tracking_notice');
		}
	}

	if(mbt_get_setting('email_subscribe_pointer') !== 'done') {
		wp_enqueue_style('wp-pointer');
		wp_enqueue_script('wp-pointer');
		add_action('admin_print_footer_scripts', 'mbt_email_subscribe_pointer');
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
		<a class="notice-button primary" href="<?php echo(admin_url('admin.php?page=mbt_help&mbt_finish_install=1')); ?>"><?php _e('Show Me How', 'mybooktable'); ?></a>
		<a class="notice-button secondary" href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_finish_install=1')); ?>"><?php _e('Thanks, I Got This', 'mybooktable'); ?></a>
	</div>
	<?php
}

function mbt_admin_setup_api_key_notice() {
	?>
	<div id="message" class="mbt-admin-notice">
		<h4><?php _e('<strong>Setup your API Key</strong> &#8211; MyBookTable needs your API key to enable enhanced features', 'mybooktable'); ?></h4>
		<a class="notice-button primary" href="<?php echo(admin_url('admin.php?page=mbt_settings')); ?>" data-mbt-track-event-override="admin_notice_setup_api_key_click"><?php _e('Go To Settings', 'mybooktable'); ?></a>
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

function mbt_admin_enable_upgrade_notice() {
	if(isset($_GET['subpage']) and $_GET['subpage'] == 'mbt_get_upgrade_page') { return; }
	?>
	<div id="message" class="mbt-admin-notice">
		<h4><?php _e('<strong>Enable your Upgrade</strong> &#8211; Download or Activate your MyBookTable Upgrade plugin to enable your advanced features!', 'mybooktable'); ?></h4>
		<a class="notice-button primary" href="<?php echo(admin_url('admin.php?page=mbt_dashboard&subpage=mbt_get_upgrade_page')); ?>" data-mbt-track-event-override="admin_notice_enable_upgrade_click"><?php _e('Enable', 'mybooktable'); ?></a>
	</div>
	<?php
}

function mbt_admin_allow_tracking_notice() {
	?>
	<div class="mbt-admin-notice">
		<h4><?php _e('<strong>Help Improve MyBookTable</strong> &#8211; Please help make MyBookTable easier to use by allowing it to gather anonymous usage statistics.', 'mybooktable'); ?></h4>
		<a class="notice-button primary" href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_allow_tracking=yes')); ?>"><?php _e('No Problem', 'mybooktable'); ?></a>
		<a class="notice-button secondary" href="<?php echo(admin_url('admin.php?page=mbt_settings&mbt_allow_tracking=no')); ?>"><?php _e('I\'d Rather Not', 'mybooktable'); ?></a>
	</div>
	<?php
}

function mbt_email_subscribe_pointer() {
	global $current_screen; global $pagenow;
	if(!($current_screen->post_type === 'mbt_book' and ((isset($_REQUEST['action']) and $_REQUEST['action'] === 'edit') or $pagenow === 'post-new.php'))) { return;}

	$current_user = wp_get_current_user();
	$email = $current_user->user_email;

	$content  = '<h3>'.__('Learn the Secrets of Amazing Author Websites', 'mybooktable').'</h3>';
	$content .= '<p>'.__('Want an author website that doesn&#39;t just look good, but also boosts book sales? Find out in this practical (and totally free) course by Author Media CEO, Thomas Umstattd Jr.', 'mybooktable').'</p>';
	$content .= '<p>'.'<input type="text" name="mbt-pointer-email" id="mbt-pointer-email" autocapitalize="off" autocorrect="off" placeholder="you@example.com" value="'.$email.'" style="width: 100%">'.'</p>';
	$content .= '<div class="mbt-pointer-buttons wp-pointer-buttons">';
	$content .= '<a id="mbt-pointer-yes" class="button-primary" style="float:left">'.__('Let&#39;s do it!', 'mybooktable').'</a>';
	$content .= '<a id="mbt-pointer-no" class="button-secondary">'.__('No, thanks', 'mybooktable').'</a>';
	$content .= '</div>';

	?>
	<script type="text/javascript">
		var mbt_email_subscribe_pointer_options = {
			pointerClass: 'mbt-email-pointer',
			content: '<?php echo($content); ?>',
			position: {edge: 'top', align: 'center'},
			buttons: function() {}
		};

		function mbt_email_subscribe_pointer_subscribe() {
			if(!/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/.test(jQuery('#mbt-pointer-email').val())) {
				jQuery('#mbt-pointer-email').addClass('error').focus();
			} else {
				mbt_track_event('admin_notice_email_subscribe_click');
				jQuery('#mbt-pointer-yes').attr('disabled', 'disabled');
				jQuery('#mbt-pointer-no').attr('disabled', 'disabled');
				jQuery('#mbt-pointer-email').attr('disabled', 'disabled');
				jQuery.post(ajaxurl,
					{
						action: 'mbt_email_subscribe_pointer',
						subscribe: 'yes',
						email: jQuery('#mbt-pointer-email').val()
					},
					function(response) {
						jQuery('.mbt-email-pointer .wp-pointer-content').html(response);
					}
				);
			}
		}

		jQuery(document).ready(function () {
			jQuery('#wpadminbar').pointer(mbt_email_subscribe_pointer_options).pointer('open');

			jQuery('#mbt-pointer-yes').click(function() {
				mbt_email_subscribe_pointer_subscribe();
			});

			jQuery('#mbt-pointer-email').keypress(function(event) {
				 if(event.which == 13) {
					mbt_email_subscribe_pointer_subscribe();
				 }
			});

			jQuery('#mbt-pointer-no').click(function() {
				mbt_track_event('admin_notice_email_subscribe_deny_click');
				jQuery.post(ajaxurl, {action: 'mbt_email_subscribe_pointer', subscribe: 'no'});
				jQuery('#wpadminbar').pointer('close');
			});

			jQuery('.mbt-email-pointer').on('click', '#mbt-pointer-close', function() {
				jQuery('#wpadminbar').pointer('close');
			});
		});
	</script>
	<?php
}

function mbt_email_subscribe_pointer_ajax() {
	if(empty($_REQUEST['subscribe'])) { die(); }
	if($_REQUEST['subscribe'] === 'yes') {
		$email = $_POST['email'];
		wp_remote_post('http://AuthorMedia.us1.list-manage1.com/subscribe/post', array(
			'method' => 'POST',
			'body' => array(
				'u' => 'b7358f48fe541fe61acdf747b',
				'id' => '6b5a675fcf',
				'MERGE0' => $email,
				'MERGE1' => '',
				'MERGE3' => '',
				'group[3045][64]' => 'on',
				'b_b7358f48fe541fe61acdf747b_6b5a675fcf' => ''
			)
		));

		$content  = '<h3>'.__('Learn the Secrets of Amazing Author Websites', 'mybooktable').'</h3>';
		$content .= '<p>'.__('Thank you for subscribing! Please check your inbox for a confirmation letter.', 'mybooktable').'</p>';
		$content .= '<div class="mbt-pointer-buttons wp-pointer-buttons">';

		$email_title = '';
		$email_link = '';
		if(strpos($email , '@yahoo') !== false) {
			$email_title = __('Go to Yahoo! Mail', 'mybooktable');
			$email_link = 'https://mail.yahoo.com/';
		} else if(strpos($email, '@hotmail') !== false) {
			$email_title = __('Go to Hotmail', 'mybooktable');
			$email_link = 'https://www.hotmail.com/';
		} else if(strpos($email, '@gmail') !== false) {
			$email_title = __('Go to Gmail', 'mybooktable');
			$email_link = 'https://mail.google.com/';
		} else if(strpos($email, '@aol') !== false) {
			$email_title = __('Go to AOL Mail', 'mybooktable');
			$email_link = 'https://mail.aol.com/';
		}
		if(!empty($email_title)) {
			$content .= '<a class="button-primary" style="float:left" href="'.$email_link.'" target="_blank">'.$email_title.'</a>';
		}

		$content .= '<a id="mbt-pointer-close" class="button-secondary">'.__('Close', 'mybooktable').'</a>';
		$content .= '</div>';
		echo($content);
	}
	mbt_update_setting('email_subscribe_pointer', 'done');
	die();
}
add_action('wp_ajax_mbt_email_subscribe_pointer', 'mbt_email_subscribe_pointer_ajax');




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
