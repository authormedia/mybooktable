<?php
/*
Script Name: 	Custom Admin Pages
Contributors: 	Tim Zook
Description: 	This will create admin pages with custom fields that will blow your mind.
Version: 		0.1
*/

/*
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

$ap_global_admin_menu_pages = array();

define('AP_CONTENT_URL', apply_filters('ap_content_url', trailingslashit(str_replace(WP_CONTENT_DIR, WP_CONTENT_URL, dirname(__FILE__)))));

function ap_enqueue_scripts($hook) {
	global $current_screen, $ap_global_admin_menu_pages;
	$pagename = ap_get_page_name($current_screen);
	if(empty($pagename)){return;}
	if(!isset($ap_global_admin_menu_pages[$pagename])){return;}

	wp_register_script('ap-timepicker', AP_CONTENT_URL . 'js/jquery.timePicker.min.js');
	wp_register_script('ap-scripts', AP_CONTENT_URL . 'js/ap.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'media-upload', 'thickbox', 'farbtastic'));
	wp_enqueue_script('ap-timepicker');
	wp_enqueue_script('ap-scripts');
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');

	wp_register_style('ap-styles', AP_CONTENT_URL . 'style.css', array('thickbox', 'farbtastic'));
	wp_enqueue_style('ap-styles');
}
add_action('admin_enqueue_scripts', 'ap_enqueue_scripts', 10);


function ap_media_selector_script()
{
	if(isset($_GET['ap_force_send']) && 'true' == $_GET['ap_force_send'])
	{ 
		$label = (isset($_GET['ap_send_label']) and !empty($_GET['ap_send_label'])) ? $_GET['ap_send_label'] : "Select File"; 

		?>
			<script type="text/javascript">
			jQuery(function($) {
				$('td.savesend input').val('<?php echo($label); ?>');
			});
			</script>
		<?php
	}
}
add_action( 'admin_print_footer_scripts', 'ap_media_selector_script', 99 );

function ap_add_theme_options_add_page($title, $id, $sections) {
	global $ap_global_admin_menu_pages;
	// Jim Camomile changed theme to options-general to make menu item load under Settings
	$ap_global_admin_menu_pages[$id] = array("type" => "options-general", "title" => $title, "id" => $id, "sections" => $sections);
}

function ap_add_global_admin_pages() {
	global $ap_global_admin_menu_pages;
	

	foreach ($ap_global_admin_menu_pages as $page) {
		// Jim Camomile changed theme to options-general to make menu item load under Settings
		if($page['type'] == "options-general")
		{
			// Jim Camomile changed add_theme_page to add_options_page to make menu item load under Settings
	//		add_options_page($page['title'], $page['title'], isset($page['capability'])?$page['capability']:'manage_options', $page['id'], 'ap_render_settings_page');
	
	// Jim Camomile added the following call to add the submenus to a custom menu for min_products post type.		
			add_submenu_page( 'edit.php?post_type=min_products', $page['title'], $page['title'], isset($page['capability'])?$page['capability']:'manage_options', $page['id'], 'ap_render_settings_page' ); 
			
			
			register_setting($page['id'].'_options', $page['id'].'_options');//, 'ap_verify_settings');

			foreach($page['sections'] as $section)
			{
				add_settings_section_with_desc($section['id'], $section['title'], 'ap_render_settings_section_desc', $page['id'].'_options_page', $section['desc']);

				foreach($section['settings_fields'] as $field)
				{
					$field['page'] = $page['id'];
					add_settings_field($field['id'], $field['name'], 'ap_render_settings_field', $page['id'].'_options_page', $section['id'], $field);
				}
			}
		}
	}
}
add_action('admin_menu', 'ap_add_global_admin_pages');

function add_settings_section_with_desc($id, $title, $callback, $page, $desc) {
	global $wp_settings_sections;

	if ( !isset($wp_settings_sections) )
		$wp_settings_sections = array();
	if ( !isset($wp_settings_sections[$page]) )
		$wp_settings_sections[$page] = array();
	if ( !isset($wp_settings_sections[$page][$id]) )
		$wp_settings_sections[$page][$id] = array();

	$wp_settings_sections[$page][$id] = array('id' => $id, 'title' => $title, 'callback' => $callback, 'desc' => $desc);
}

function ap_get_page_name($screen)
{
//var_dump($screen->id);
	if(substr($screen->id, 0, 16) == "appearance_page_"){return substr($screen->id, 16);}
	if(substr($screen->id, 0, 11) == "admin_page_"){return substr($screen->id, 11);}
	// added by Jim Camomile
	if(substr($screen->id, 0, 14) == "settings_page_"){return substr($screen->id, 14);}
	if(substr($screen->id, 0, 18) == "min_products_page_"){return substr($screen->id, 18);}
	return '';
}

function ap_render_settings_page()
{
	global $current_screen, $ap_global_admin_menu_pages;
	$pagename = ap_get_page_name($current_screen);
	//var_dump($pagename);
	if(empty($pagename)){return;}

	$page = $ap_global_admin_menu_pages[$pagename];

	?>

	<div class="ap_wrap">
		<?php screen_icon(); ?>
		<h1 class="options-title"><?php echo($page['title']); ?></h1>
		<?php settings_errors(); ?>
		<form id="options-form" method="post" action="options.php" enctype="multipart/form-data" encoding="multipart/form-data">
			<?php
				settings_fields($page['id'].'_options');
				do_settings_sections($page['id'].'_options_page');
				submit_button();
			?>
		</form>
	</div>

	<?php
}

function ap_render_settings_section_desc($section) {
	if(isset($section['desc']) and !empty($section['desc'])){echo($section['desc']);}
}

function ap_render_settings_field($field) {
	$options = get_option($field['page'].'_options');
	$value = isset($options[$field['id']]) ? $options[$field['id']] : '';
	$name = $field['page'].'_options['.$field['id'].']';
	$default = isset($default) ? $default : '';

	switch($field['type']) {
		case 'text':
			echo '<input type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? $value : $default, '" />','<p class="ap_field_description">', $field['desc'], '</p>';
			break;
		case 'text_small':
			echo '<input class="ap_text_small" type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? $value : $default, '" /><span class="ap_field_description">', $field['desc'], '</span>';
			break;
		case 'text_medium':
			echo '<input class="ap_text_medium" type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? $value : $default, '" /><span class="ap_field_description">', $field['desc'], '</span>';
			break;
		case 'text_date':
			echo '<input class="ap_text_small ap_datepicker" type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? $value : $default, '" /><span class="ap_field_description">', $field['desc'], '</span>';
			break;
		case 'text_date_timestamp':
			echo '<input class="ap_text_small ap_datepicker" type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? date( 'm\/d\/Y', $value ) : $default, '" /><span class="ap_field_description">', $field['desc'], '</span>';
			break;
		case 'text_datetime':
			if(!is_array($value)){$value = array();}
			echo '<input class="ap_text_small ap_datepicker" type="text" name="', $name, '[date]" id="', $field['id'], '_date" value="', (isset($value['date']) and '' !== $value['date']) ? $value['date'] : $default, '" />';
			echo '<input class="ap_timepicker text_time" type="text" name="', $name, '[time]" id="', $field['id'], '_time" value="', (isset($value['date']) and '' !== $value['date']) ? $value['date'] : $default, '" /><span class="ap_field_description" >', $field['desc'], '</span>';
			break;
		case 'text_datetime_timestamp':
			echo '<input class="ap_text_small ap_datepicker" type="text" name="', $name, '[date]" id="', $field['id'], '_date" value="', '' !== $value ? date( 'm\/d\/Y', $value ) : $default, '" />';
			echo '<input class="ap_timepicker text_time" type="text" name="', $name, '[time]" id="', $field['id'], '_time" value="', '' !== $value ? date( 'h:i A', $value ) : $default, '" /><span class="ap_field_description" >', $field['desc'], '</span>';
			break;
		case 'text_time':
			echo '<input class="ap_timepicker text_time" type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? $value : $default, '" /><span class="ap_field_description">', $field['desc'], '</span>';
			break;
		case 'text_money':
			echo '$ <input class="ap_text_money" type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? $value : $default, '" /><span class="ap_field_description">', $field['desc'], '</span>';
			break;
		case 'colorpicker':
			$value = '' !== $value ? $value : $default;
			$hex_color = '(([a-fA-F0-9]){3}){1,2}$';
			if ( preg_match( '/^' . $hex_color . '/i', $value ) ) // Value is just 123abc, so prepend #.
				$value = '#' . $value;
			elseif ( ! preg_match( '/^#' . $hex_color . '/i', $value ) ) // Value doesn't match #123abc, so sanitize to just #.
				$value = "#";
			echo '<input class="ap_colorpicker ap_text_small" type="text" name="', $name, '" id="', $field['id'], '" value="', $value, '" /><span class="ap_field_description">', $field['desc'], '</span>';
			break;
		case 'textarea':
			echo '<textarea name="', $name, '" id="', $field['id'], '" cols="60" rows="10">', '' !== $value ? $value : $default, '</textarea>','<p class="ap_field_description">', $field['desc'], '</p>';
			break;
		case 'textarea_small':
			echo '<textarea name="', $name, '" id="', $field['id'], '" cols="60" rows="4">', '' !== $value ? $value : $default, '</textarea>','<p class="ap_field_description">', $field['desc'], '</p>';
			break;
		case 'textarea_code':
			echo '<textarea name="', $name, '" id="', $field['id'], '" cols="60" rows="10" class="ap_textarea_code">', '' !== $value ? $value : $default, '</textarea>','<p class="ap_field_description">', $field['desc'], '</p>';
			break;
		case 'select':
			if( empty( $value ) && !empty( $default ) ) $value = $default;
			echo '<select name="', $name, '" id="', $field['id'], '">';
			foreach ($field['options'] as $option) {
				echo '<option value="', $option['value'], '"', $value == $option['value'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
			}
			echo '</select>';
			echo '<p class="ap_field_description">', $field['desc'], '</p>';
			break;
		case 'radio_inline':
			if( empty( $value ) && !empty( $default ) ) $value = $default;
			echo '<div class="ap_radio_inline">';
			$i = 1;
			foreach ($field['options'] as $option) {
				echo '<div class="ap_radio_inline_option"><input type="radio" name="', $name, '" id="', $field['id'], $i, '" value="', $option['value'], '"', $value == $option['value'] ? ' checked="checked"' : '', ' /><label for="', $field['id'], $i, '">', $option['name'], '</label></div>';
				$i++;
			}
			echo '</div>';
			echo '<p class="ap_field_description">', $field['desc'], '</p>';
			break;
		case 'radio':
			if( empty( $value ) && !empty( $default ) ) $value = $default;
			echo '<ul>';
			$i = 1;
			foreach ($field['options'] as $option) {
				echo '<li><input type="radio" name="', $name, '" id="', $field['id'], $i,'" value="', $option['value'], '"', $value == $option['value'] ? ' checked="checked"' : '', ' /><label for="', $field['id'], $i, '">', $option['name'].'</label></li>';
				$i++;
			}
			echo '</ul>';
			echo '<p class="ap_field_description">', $field['desc'], '</p>';
			break;
		case 'checkbox':
			echo '<input type="checkbox" name="', $name, '" id="', $field['id'], '"', $value ? ' checked="checked"' : '', ' />';
			echo '<span class="ap_field_description">', $field['desc'], '</span>';
			break;
		case 'multicheck':
			echo '<ul>';
			$i = 1;
			if($value == ''){$value = array();}
			foreach ( $field['options'] as $key => $name ) {
				// Append `[]` to the name to get multiple values
				// Use in_array() to check whether the current option should be checked
				echo '<li><input type="checkbox" name="', $name, '[]" id="', $field['id'], $i, '" value="', $key, '"', in_array($key, $value) ? ' checked="checked"' : '', ' /><label for="', $field['id'], $i, '">', $name, '</label></li>';	
				$i++;
			}
			echo '</ul>';
			echo '<span class="ap_field_description">', $field['desc'], '</span>';
			break;
		case 'wysiwyg':
			wp_editor( $value ? $value : $default, $name, isset( $field['options'] ) ? $field['options'] : array() );
	        echo '<p class="ap_field_description">', $field['desc'], '</p>';
			break;
		case 'file':
			$input_type_url = "hidden";
			if(isset($field['allow'])) {
				if('url' == $field['allow'] || (is_array($field['allow']) && in_array('url', $field['allow']))) {
					$input_type_url="text";
				}
			}
			echo '<input class="ap_upload_file" type="' . $input_type_url . '" size="45" id="', $field['id'], '" name="', $name, '" value="', $value, '" />';
			echo '<input class="ap_upload_button button" type="button" name="', $field['name'], '" value="Upload File" />';
			echo '<p class="ap_field_description">', $field['desc'], '</p>';
			echo '<div id="', $field['id'], '_status" class="ap_upload_status">';
				if ( $value != '' ) { 
					$check_image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $value );
					if ( $check_image ) {
						echo '<div class="img_status">';
						echo '<img src="', $value, '" alt="" />';
						echo '<a href="#" class="ap_remove_file_button" rel="', $field['id'], '">Remove Image</a>';
						echo '</div>';
					} else {
						$parts = explode( '/', $value );
						for( $i = 0; $i < count( $parts ); ++$i ) {
							$title = $parts[$i];
						}
						echo 'File: <strong>', $title, '</strong>&nbsp;&nbsp;&nbsp; (<a href="', $value, '" target="_blank" rel="external">Download</a> / <a href="#" class="ap_remove_file_button" rel="', $field['id'], '">Remove</a>)';
					}
				}
			echo '</div>'; 
			break;
		default:
			do_action('ap_render_settings_field_' . $field['type'] , $field, $value);
	}
}

/*function ap_verify_settings($input) {
	global $current_screen, $ap_global_admin_menu_pages;
	$pagename = ap_get_page_name($current_screen);
	if(empty($pagename)){return;}

	$page = $ap_global_admin_menu_pages[$pagename];

	$output = array("herp" => "derp");

	foreach($page['sections'] as $section)
	{
		foreach($section['settings_fields'] as $field)
		{
			$new = isset($input[$field['id']]) ? $input[$field['id']] : (isset($field['default'])?$field['default']:'');

			if(($field['type'] == 'textarea') || ($field['type'] == 'textarea_small')) {
				$new = htmlspecialchars($new);
			}

			if(($field['type'] == 'textarea_code')) {
				$new = htmlspecialchars_decode($new);
			}
			
			if($field['type'] == 'text_date_timestamp') {
				$new = strtotime($new);
			}

			if($field['type'] == 'text_datetime_timestamp') {
				$string = $new['date'] . ' ' . $new['time'];
				$new = strtotime($string);
			}
			
			$new = apply_filters('cmb_validate_' . $field['type'], $new, $page, $field);

			$output[$field['id']] = $new;
		}
	}

	return $output;
}*/

?>