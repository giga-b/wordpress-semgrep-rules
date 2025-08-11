<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

get_header();

if ( \Voxel\get_page_setting( 'voxel_hide_header' ) !== 'yes' ) {
	\Voxel\print_header();
}

if ( have_posts() ):
	while ( have_posts() ): the_post();
		the_content();
	endwhile;
endif;

if ( \Voxel\get_page_setting( 'voxel_hide_footer' ) !== 'yes' ) {
	\Voxel\print_footer();
}

get_footer();