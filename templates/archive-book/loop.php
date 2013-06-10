<?php

do_action('mbt_before_book_archive_loop');
while(have_posts()) {
	the_post();
	do_action('mbt_book_excerpt');
}
do_action('mbt_after_book_archive_loop');