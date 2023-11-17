<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( is_user_logged_in() ) {
		get_header();
		the_post();
		do_action('ttbm_booking_details_page',get_the_id());
		do_action('ttbm_booking_details_page_footer',get_the_id());
		get_footer();
		
	}