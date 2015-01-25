<?php

do_action('mbt_before_single_book');
do_action('mbt_single_book_images');
?><div class="mbt-book-right"><?php
do_action('mbt_single_book_title');
do_action('mbt_single_book_price');
do_action('mbt_single_book_meta');
do_action('mbt_single_book_blurb');
do_action('mbt_single_book_buybuttons');
?></div><?php
do_action('mbt_single_book_overview');
do_action('mbt_single_book_socialmedia');
do_action('mbt_after_single_book');
