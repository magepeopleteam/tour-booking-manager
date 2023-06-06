<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	if ( TTBM_Function::get_post_info( $ttbm_post_id, 'ttbm_display_slider', 'on' ) != 'off' ) {
		do_action( 'add_super_slider', $ttbm_post_id ,'ttbm_gallery_images' );
	}
