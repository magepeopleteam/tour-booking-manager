<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$tour_id = $tour_id ?? get_the_id();
	if ( TTBM_Function::get_post_info( $tour_id, 'ttbm_display_slider', 'on' ) != 'off' ) {
		do_action( 'add_super_slider', $tour_id ,'ttbm_gallery_images' );
	}
