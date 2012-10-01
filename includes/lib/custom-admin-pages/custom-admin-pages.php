<?php
/*
Script Name: 	Custom Admin Pages
Contributors: 	Tim Zook
Description: 	This will create admin pages with custom fields that will blow your mind.
Version: 		0.4
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

if(!defined('CAP_VERSION')) {
	define('CAP_VERSION', 0.4);

	$cap_global_admin_menu_pages = array();

	define('CAP_CONTENT_URL', apply_filters('cap_content_url', trailingslashit(str_replace(str_replace("\\", "/", WP_CONTENT_DIR), str_replace('\\', '/', WP_CONTENT_URL), str_replace('\\', '/', dirname(__FILE__))))));

	function cap_enqueue_scripts($hook) {
		global $cap_global_admin_menu_pages;
		$pagename = cap_get_page_name();
		if(empty($pagename)){return;}
		if(!isset($cap_global_admin_menu_pages[$pagename])){return;}

		wp_register_script('cap-timepicker', CAP_CONTENT_URL.'js/jquery.timePicker.min.js');
		wp_register_script('cap-scripts', CAP_CONTENT_URL.'js/cap.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'media-upload', 'thickbox', 'farbtastic'));
		wp_enqueue_script("jquery");
		wp_enqueue_script("jquery-ui-core");
		wp_enqueue_script("jquery-ui-datepicker");
		wp_enqueue_script('cap-scripts');
		wp_enqueue_script('cap-timepicker');
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');

		wp_register_style('ap-styles', CAP_CONTENT_URL.'style.css', array('thickbox', 'farbtastic'));
		wp_enqueue_style('ap-styles');
	}
	add_action('admin_enqueue_scripts', 'cap_enqueue_scripts', 10);

	function cap_media_selector_script() {
		if(isset( $_GET['cap_force_send']) && 'true' == $_GET['cap_force_send']) { 
			$label = $_GET['cap_send_label']; 
			if(empty($label)) $label="Select File";
			?>	
				<script type="text/javascript">
				jQuery(function($) {
					$('td.savesend input').val('<?php echo $label; ?>');
				});
				</script>
			<?php 
		}
	}
	add_action( 'admin_print_footer_scripts', 'cap_media_selector_script', 99);

	function cap_add_menu_page($title, $id, $sections, $capability='', $icon='', $position='') {
		global $cap_global_admin_menu_pages;
		$cap_global_admin_menu_pages[$id] = array("type" => "menu", "title" => $title, "id" => $id, "sections" => $sections);
		if(!empty($capability)){$cap_global_admin_menu_pages[$id]['capability'] = $capability;}
		if(!empty($icon)){$cap_global_admin_menu_pages[$id]['icon'] = $icon;}
		if(!empty($position)){$cap_global_admin_menu_pages[$id]['position'] = $position;}
	}

	function cap_add_submenu_page($parent, $title, $id, $sections, $capability='') {
		global $cap_global_admin_menu_pages;
		$cap_global_admin_menu_pages[$id] = array("type" => "submenu", "parent" => $parent, "title" => $title, "id" => $id, "sections" => $sections);
		if(!empty($capability)){$cap_global_admin_menu_pages[$id]['capability'] = $capability;}
	}

	function cap_add_theme_page($title, $id, $sections, $capability='') {
		global $cap_global_admin_menu_pages;
		$cap_global_admin_menu_pages[$id] = array("type" => "submenu", "parent" => "themes.php", "title" => $title, "id" => $id, "sections" => $sections);
		if(!empty($capability)){$cap_global_admin_menu_pages[$id]['capability'] = $capability;}
	}

	function cap_add_settings_page($title, $id, $sections, $capability='') {
		global $cap_global_admin_menu_pages;
		$cap_global_admin_menu_pages[$id] = array("type" => "submenu", "parent" => "options-general.php", "title" => $title, "id" => $id, "sections" => $sections);
		if(!empty($capability)){$cap_global_admin_menu_pages[$id]['capability'] = $capability;}
	}

	function cap_add_tools_page($title, $id, $sections, $capability='') {
		global $cap_global_admin_menu_pages;
		$cap_global_admin_menu_pages[$id] = array("type" => "submenu", "parent" => "tools.php", "title" => $title, "id" => $id, "sections" => $sections);
		if(!empty($capability)){$cap_global_admin_menu_pages[$id]['capability'] = $capability;}
	}

	function cap_get_option($page_id)
	{
		return get_option('cap_'.$page_id);
	}

	function cap_get_option_field($page_id, $field_id)
	{
		$option = get_option('cap_'.$page_id);
		return isset($option[$field_id]) ? $option[$field_id] : NULL;
	}

	function cap_update_option($page_id, $value)
	{
		return update_option('cap_'.$page_id, $value);
	}

	function cap_add_global_admin_pages() {
		global $cap_global_admin_menu_pages;

		foreach ($cap_global_admin_menu_pages as $page) {
			if($page['type'] == 'submenu')
			{
				add_submenu_page($page['parent'], $page['title'], $page['title'], isset($page['capability'])?$page['capability']:'manage_options', $page['id'], 'cap_render_admin_page');
			}
			else if($page['type'] == 'menu')
			{
				add_menu_page($page['title'], $page['title'], isset($page['capability'])?$page['capability']:'manage_options', $page['id'], 'cap_render_admin_page', isset($page['icon'])?$page['icon']:NULL, isset($page['position'])?$page['position']:NULL);
			}

			$page['option'] = 'cap_'.$page['id'];
			register_setting($page['id'].'_group', $page['option'], 'cap_verify_settings');

			foreach($page['sections'] as $section) {
				add_settings_section_with_desc($section['id'], $section['title'], 'cap_render_settings_section_desc', $page['id'].'_options_page', $section['desc']);

				foreach($section['settings_fields'] as $field) {
					$field['option'] = $page['option'];
					add_settings_field($field['id'], $field['name'], 'cap_render_settings_field', $page['id'].'_options_page', $section['id'], $field);
				}
			}
		}
	}
	add_action('admin_menu', 'cap_add_global_admin_pages');

	function add_settings_section_with_desc($id, $title, $callback, $page, $desc) {
		global $wp_settings_sections;

		if (!isset($wp_settings_sections))
			$wp_settings_sections = array();
		if (!isset($wp_settings_sections[$page]))
			$wp_settings_sections[$page] = array();
		if (!isset($wp_settings_sections[$page][$id]))
			$wp_settings_sections[$page][$id] = array();

		$wp_settings_sections[$page][$id] = array('id' => $id, 'title' => $title, 'callback' => $callback, 'desc' => $desc);
	}

	function cap_get_page_name()
	{
		global $plugin_page;
		if(isset($plugin_page)){return $plugin_page;}
		if(isset($_POST['option_page'])){return substr($_POST['option_page'], 0, -6);}
		return '';
	}

	function cap_render_admin_page()
	{
		global $cap_global_admin_menu_pages;

		$pagename = cap_get_page_name();
		if(empty($pagename)){return;}

		$page = $cap_global_admin_menu_pages[$pagename];

		?>

		<div class="cap_wrap">
			<?php screen_icon(); ?>
			<h1 class="options-title"><?php echo($page['title']); ?></h1>
			<?php settings_errors(); ?>
			<form id="options-form" method="post" action="options.php" enctype="multipart/form-data" encoding="multipart/form-data">
				<?php
					settings_fields($page['id'].'_group');
					do_settings_sections($page['id'].'_options_page');
					submit_button();
				?>
			</form>
		</div>

		<?php
	}

	function cap_render_settings_section_desc($section) {
		if(isset($section['desc']) and !empty($section['desc'])){echo($section['desc']);}
	}

	function cap_render_settings_field($field) {
		$options = get_option($field['option']);
		$value = isset($options[$field['id']]) ? $options[$field['id']] : '';
		$name = $field['option'].'['.$field['id'].']';
		$default = isset($field['default']) ? $field['default'] : '';

		switch($field['type']) {
			case 'text':
				echo '<input type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? $value : $default, '" />','<p class="cap_field_description">', $field['desc'], '</p>';
				break;
			case 'text_small':
				echo '<input class="cap_text_small" type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? $value : $default, '" /><span class="cap_field_description">', $field['desc'], '</span>';
				break;
			case 'text_medium':
				echo '<input class="cap_text_medium" type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? $value : $default, '" /><span class="cap_field_description">', $field['desc'], '</span>';
				break;
			case 'text_date':
				echo '<input class="cap_text_small cap_datepicker" type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? $value : $default, '" /><span class="cap_field_description">', $field['desc'], '</span>';
				break;
			case 'text_date_timestamp':
				echo '<input class="cap_text_small cap_datepicker" type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? date('m\/d\/Y', $value) : $default, '" /><span class="cap_field_description">', $field['desc'], '</span>';
				break;
			case 'text_datetime':
				if(!is_array($value)){$value = array();}
				echo '<input class="cap_text_small cap_datepicker" type="text" name="', $name, '[date]" id="', $field['id'], '_date" value="', (isset($value['date']) and '' !== $value['date']) ? $value['date'] : $default, '" />';
				echo '<input class="cap_timepicker text_time" type="text" name="', $name, '[time]" id="', $field['id'], '_time" value="', (isset($value['time']) and '' !== $value['time']) ? $value['time'] : $default, '" /><span class="cap_field_description" >', $field['desc'], '</span>';
				break;
			case 'text_datetime_timestamp':
				echo '<input class="cap_text_small cap_datepicker" type="text" name="', $name, '[date]" id="', $field['id'], '_date" value="', '' !== $value ? date('m\/d\/Y', $value) : $default, '" />';
				echo '<input class="cap_timepicker text_time" type="text" name="', $name, '[time]" id="', $field['id'], '_time" value="', '' !== $value ? date('h:i A', $value) : $default, '" /><span class="cap_field_description" >', $field['desc'], '</span>';
				break;
			case 'text_time':
				echo '<input class="cap_timepicker text_time" type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? $value : $default, '" /><span class="cap_field_description">', $field['desc'], '</span>';
				break;
			case 'text_money':
				echo '$ <input class="cap_text_money" type="text" name="', $name, '" id="', $field['id'], '" value="', '' !== $value ? $value : $default, '" /><span class="cap_field_description">', $field['desc'], '</span>';
				break;
			case 'colorpicker':
				$value = '' !== $value ? $value : $default;
				$hex_color = '(([a-fA-F0-9]){3}){1,2}$';
				if(preg_match('/^'.$hex_color.'/i',$value)) {
					$value = '#'.$value;
				} elseif(!preg_match('/^#'.$hex_color.'/i', $value)) {
					$value = "#";
				}
				echo '<input class="cap_colorpicker cap_text_small" type="text" name="', $name, '" id="', $field['id'], '" value="', $value, '" /><span class="cap_field_description">', $field['desc'], '</span>';
				break;
			case 'textarea':
				echo '<textarea name="', $name, '" id="', $field['id'], '" cols="60" rows="10">', '' !== $value ? $value : $default, '</textarea>','<p class="cap_field_description">', $field['desc'], '</p>';
				break;
			case 'textarea_small':
				echo '<textarea name="', $name, '" id="', $field['id'], '" cols="60" rows="4">', '' !== $value ? $value : $default, '</textarea>','<p class="cap_field_description">', $field['desc'], '</p>';
				break;
			case 'textarea_code':
				echo '<textarea name="', $name, '" id="', $field['id'], '" cols="60" rows="10" class="cap_textarea_code">', '' !== $value ? $value : $default, '</textarea>','<p class="cap_field_description">', $field['desc'], '</p>';
				break;
			case 'select':
				if(empty($value) && !empty($default)) $value = $default;
				echo '<select name="', $name, '" id="', $field['id'], '">';
				foreach($field['options'] as $option) {
					echo '<option value="', $option['value'], '"', $value == $option['value'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
				}
				echo '</select>';
				echo '<p class="cap_field_description">', $field['desc'], '</p>';
				break;
			case 'radio_inline':
				if( empty( $value ) && !empty( $default ) ) $value = $default;
				echo '<div class="cap_radio_inline">';
				$i = 1;
				foreach ($field['options'] as $option) {
					echo '<div class="cap_radio_inline_option"><input type="radio" name="', $name, '" id="', $field['id'], $i, '" value="', $option['value'], '"', $value == $option['value'] ? ' checked="checked"' : '', ' /><label for="', $field['id'], $i, '">', $option['name'], '</label></div>';
					$i++;
				}
				echo '</div>';
				echo '<p class="cap_field_description">', $field['desc'], '</p>';
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
				echo '<p class="cap_field_description">', $field['desc'], '</p>';
				break;
			case 'checkbox':
				echo '<input type="checkbox" name="', $name, '" id="', $field['id'], '"', $value ? ' checked="checked"' : '', ' />';
				echo '<span class="cap_field_description">', $field['desc'], '</span>';
				break;
			case 'multicheck':
				echo '<ul>';
				$i = 1;
				if($value == ''){$value = array();}
				foreach($field['options'] as $key => $name) {
					echo '<li><input type="checkbox" name="', $name, '[]" id="', $field['id'], $i, '" value="', $key, '"', in_array($key, $value) ? ' checked="checked"' : '', ' /><label for="', $field['id'], $i, '">', $name, '</label></li>';	
					$i++;
				}
				echo '</ul>';
				echo '<span class="cap_field_description">', $field['desc'], '</span>';
				break;
			case 'page':
				if(empty($value) && !empty($default)) $value = $default;
				echo '<select name="', $name, '" id="', $field['id'], '">';
				echo '<option value=""', $value == '' ? ' selected="selected"' : '', '> -- Choose One -- </option>';
				foreach(get_pages() as $page) {
					echo '<option value="', $page->ID, '"', $value == $page->ID ? ' selected="selected"' : '', '>', $page->post_title, '</option>';
				}
				echo '</select>';
				echo '<p class="cap_field_description">', $field['desc'], '</p>';
				break;
			case 'wysiwyg':
				wp_editor( $value ? $value : $default, $name, isset($field['options']) ? $field['options'] : array());
		        echo '<p class="cap_field_description">', $field['desc'], '</p>';
				break;
			case 'file':
				$input_type_url = "hidden";
				if(isset($field['allow'])) {
					if('url' == $field['allow'] || (is_array($field['allow']) && in_array('url', $field['allow']))) {
						$input_type_url="text";
					}
				}
				echo '<input class="cap_upload_file" type="' . $input_type_url . '" size="45" id="', $field['id'], '" name="', $name, '" value="', $value, '" />';
				echo '<input class="cap_upload_button button" type="button" name="', $field['name'], '" value="Upload File" />';
				echo '<p class="cap_field_description">', $field['desc'], '</p>';
				echo '<div id="', $field['id'], '_status" class="cap_upload_status">';
					if($value != '') {
						$check_image = preg_match('/(^.*\.jpg|jpeg|png|gif|ico*)/i', $value);
						if($check_image) {
							echo '<div class="img_status">';
							echo '<img src="', $value, '" alt="" />';
							echo '<a href="#" class="cap_remove_file_button" rel="', $field['id'], '">Remove Image</a>';
							echo '</div>';
						} else {
							$parts = explode('/', $value);
							for($i = 0; $i < count($parts); ++$i) {
								$title = $parts[$i];
							}
							echo 'File: <strong>', $title, '</strong>&nbsp;&nbsp;&nbsp; (<a href="', $value, '" target="_blank" rel="external">Download</a> / <a href="#" class="cap_remove_file_button" rel="', $field['id'], '">Remove</a>)';
						}
					}
				echo '</div>';
				break;
			default:
				do_action('cap_render_settings_field_' . $field['type'] , $field, $value);
		}
	}

	function cap_verify_settings($input) {
		global $cap_global_admin_menu_pages;
		$pagename = cap_get_page_name();
		if(empty($pagename)){return;}

		$page = $cap_global_admin_menu_pages[$pagename];

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
				
				$new = apply_filters('cap_validate_' . $field['type'], $new, $page, $field);

				$output[$field['id']] = $new;
			}
		}

		return $output;
	}
}