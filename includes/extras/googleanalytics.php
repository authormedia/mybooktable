<?php

function mbt_buybutton_button_add_ga($output, $data, $store) {
	global $post;
	if(!empty($post->post_title)) {
		$ga = 'onclick="if(typeof window._gaq !== \'undefined\'){window._gaq.push([\'_trackEvent\', \'MyBookTable\', \''.$store['name'].' Buy Button Click\', \''.$post->post_title.'\']);}"';
		$output = preg_replace('/<a/', '<a '.$ga, $output);
	}
	return $output;
}
add_filter('mbt_format_buybutton', 'mbt_buybutton_button_add_ga', 10, 3);
