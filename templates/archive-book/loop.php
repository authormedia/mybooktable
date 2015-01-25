<?php

do_action('mbt_before_book_archive_loop');
?> <div class="mbt-book-archive-books"> <?php
while(have_posts()) {
	the_post();
	do_action('mbt_book_excerpt');
}
?> </div> <?php
do_action('mbt_after_book_archive_loop');