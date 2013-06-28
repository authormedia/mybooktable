<?php

do_action('mbt_before_book_excerpt');
do_action('mbt_book_excerpt_images');
?><div class="mbt-book-right"><?php
do_action('mbt_book_excerpt_title');
do_action('mbt_book_excerpt_price');
do_action('mbt_book_excerpt_meta');
do_action('mbt_book_excerpt_blurb');
do_action('mbt_book_excerpt_buybuttons');
do_action('mbt_book_excerpt_socialmedia');
?></div><?php
do_action('mbt_after_book_excerpt');
