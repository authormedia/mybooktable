<?php

/*---------------------------------------------------------*/
/* Affiliates Metabox                                      */
/*---------------------------------------------------------*/

function bss_add_affiliates_metabox()
{
	add_meta_box('mbt_affiliates', 'Affiliates','mbt_affiliates_metabox', 'mbt_products', 'normal');
}
add_action('add_meta_boxes', 'bss_add_affiliates_metabox');

function mbt_affiliates_metabox_ajax() {
	echo('<div class="mbt_affiliate_editor">');
	echo('<button class="mbt_affiliate_remover" style="float:right">Remove</button>');
	$affiliates = mbt_get_affiliates();
	echo($affiliates[$_POST['type']]['editor'](array('type' => $_POST['type'], 'value' => ''), "mbt_affiliate".$_POST['num'], $affiliates));
	echo('</div>');
	die();
}
add_action('wp_ajax_mbt_affiliates_metabox', 'mbt_affiliates_metabox_ajax');

function mbt_affiliates_metabox($post)
{
	wp_nonce_field(plugin_basename(__FILE__), 'mbt_nonce');

	?>

	<script type="text/javascript">
		jQuery(document).ready(function() {
			var adding = false;

			function reset_numbers() {
				jQuery('#mbt_affiliate_editors .mbt_affiliate_editor').each(function(i) {
					jQuery(this).find("input, textarea, select").each(function() {
						jQuery(this).attr('name', jQuery(this).attr('name').replace(/mbt_affiliate\d*\[([A-Za-z0-9]*)\]/, "mbt_affiliate"+i+"[$1]"));
					});
				});
			}

			jQuery('#mbt_affiliate_adder').click(function() {
				if(!adding) {
					adding = true;
					jQuery.post(ajaxurl,
						{
							action: 'mbt_affiliates_metabox',
							type: jQuery('#mbt_affiliate_selector').val(),
							num: 0
						},
						function(response) {
							var element = jQuery(response);
							jQuery("#mbt_affiliate_editors").prepend(element);
							reset_numbers();
							adding = false;
						}
					);
				}
				return false;
			});

			jQuery("#mbt_affiliate_editors").on("click", ".mbt_affiliate_remover", function() {
				jQuery(this).parent().remove();
				reset_numbers();
			});
		});
	</script>

	<?php

	$affiliates = mbt_get_affiliates();
	echo('Choose One:');
	echo('<select id="mbt_affiliate_selector">');
	foreach($affiliates as $slug => $affiliate) {
  		echo('<option value="'.$slug.'">'.$affiliate['name'].'</option>');
  	}
	echo('</select>');
	echo('<button id="mbt_affiliate_adder">Add</button>');

	echo('<div id="mbt_affiliate_editors">');
	$post_affiliates = get_post_meta($post->ID, "mbt_affiliates", true);
	if(!empty($post_affiliates)) {
		for($i = 0; $i < count($post_affiliates); $i++)
		{
			echo('<div class="mbt_affiliate_editor">');
			echo('<button class="mbt_affiliate_remover" style="float:right">Remove</button>');
			echo($affiliates[$post_affiliates[$i]['type']]['editor']($post_affiliates[$i], "mbt_affiliate".$i, $affiliates));
			echo('</div>');
		}
	}
	echo('</div>');
}

function mbt_save_affiliates($post_id)
{
	if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !isset($_POST['mbt_nonce']) || !wp_verify_nonce($_POST['mbt_nonce'], plugin_basename(__FILE__))){return;}

	if(get_post_type($post_id) == "mbt_products")
	{
		$mydata = array();
		for($i = 0; isset($_POST['mbt_affiliate'.$i]); $i++)
		{
			$mydata[] = $_POST['mbt_affiliate'.$i];
		}
		update_post_meta($post_id, "mbt_affiliates", $mydata);
	}
}
add_action('save_post', 'mbt_save_affiliates');



/*---------------------------------------------------------*/
/* Metadata Metabox                                        */
/*---------------------------------------------------------*/

//Initialize library
add_action('init', 'mbt_initialize_cmb_meta_boxes', 9999);
function mbt_initialize_cmb_meta_boxes(){ if(!class_exists('cmb_Meta_Box')){ require_once('lib/metabox/init.php'); } }

//Add metaboxes
add_filter('cmb_meta_boxes', 'mbt_add_metaboxes');
function mbt_add_metaboxes($meta_boxes) {
	$meta_boxes[] = array(
		'id'         => 'product_info',
		'title'      => 'Book Metadata',
		'pages'      => array('mbt_products'),
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true,
		'fields'     => array(
			array(
				'name' => 'Book ID',
				'desc' => 'SKU or Unique ID',
				'id'   => 'mbt_sku',
				'type' => 'text_small',
			),
			array(
				'name' => 'Book Display Price',
				'desc' => 'Optional',
				'id'   => 'mbt_price',
				'type' => 'text_money',
			),					
			array(
				'name' => 'Old Book Price',
				'desc' => 'Optional field to display a previous, crossed out book price',
				'id'   => 'mbt_old_price',
				'type' => 'text_money',
			),
			array(
				'name' => 'Author',
				'desc' => 'optional (for book or other published content)',
				'id'   => 'mbt_author',
				'type' => 'text_small',
			),
			array(
				'name' => 'Additional Description',
				'desc' => 'short additional description below the add-to-cart button (optional)',
				'id'   => 'mbt_additional_description',
				'type' => 'wysiwyg',
			),
			array(
				'name' => 'Featured Book',
				'desc' => 'Show in the Featured Books sidebar widget ',
				'id'   => 'mbt_featured',
				'type' => 'checkbox',
			),				
			array(
				'name' => 'Issuu Document ID',
				'desc' => 'the documentID of the Book for Issuu. This is a ridiculously long code like: 091013542540-7ccfb3de03724177a1d21fvdadc354a6',
				'id'   => 'mbt_issue_doc_id',
				'type' => 'text',
			),
		),
	);
	return $meta_boxes;
}