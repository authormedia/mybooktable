<?php

do_action('mbt_before_book_archive');
do_action('mbt_book_archive_header');
if(have_posts()) {
	do_action('mbt_book_archive_loop');
} else {
	do_action('mbt_book_archive_no_results');
}
do_action('mbt_after_book_archive');