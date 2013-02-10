<?php
/*
 * Template Name: Single Book Page
 */

get_header();
do_action('mbt_before_main_content');
do_action('mbt_before_single_book');
do_action('mbt_single_book_images');
do_action('mbt_single_book_title');
do_action('mbt_single_book_price');
do_action('mbt_single_book_meta');
do_action('mbt_single_book_blurb');
do_action('mbt_single_book_buybuttons');
do_action('mbt_single_book_overview');
do_action('mbt_single_book_socialmedia');
do_action('mbt_single_book_series');
do_action('mbt_after_single_book');
do_action('mbt_after_main_content');
get_footer();
