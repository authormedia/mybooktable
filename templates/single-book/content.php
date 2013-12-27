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
if(!mbt_get_setting('hide_domc_notice')){ ?><div class="mbt-affiliate-disclaimer"><?php _e('Disclosure of Material Connection: Some of the links in the page above are "affiliate links." This means if you click on the link and purchase the item, I will receive an affiliate commission. I am disclosing this in accordance with the Federal Trade Commission\'s <a href="http://www.access.gpo.gov/nara/cfr/waisidx_03/16cfr255_03.html" target="_blank">16 CFR, Part 255</a>: "Guides Concerning the Use of Endorsements and Testimonials in Advertising."', 'mybooktable'); ?></div><?php }
do_action('mbt_after_single_book');
