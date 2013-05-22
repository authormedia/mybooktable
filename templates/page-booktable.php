<?php
/*
 * Template Name: Book Table Page
 */

get_header();
do_action('mbt_before_booktable');
do_action('mbt_before_main_content');
do_action('mbt_before_book_archive');

if(have_posts()) {
	do_action('mbt_book_archive_header');

	do_action('mbt_before_book_archive_listing');
	while(have_posts()) {
		the_post();
		do_action('mbt_book_excerpt');
	}
	do_action('mbt_after_book_archive_listing');
} else {
	do_action('mbt_book_archive_no_results');
}

do_action('mbt_after_book_archive');
do_action('mbt_after_main_content');
do_action('mbt_after_booktable');
get_footer();
