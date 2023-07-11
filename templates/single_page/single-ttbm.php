<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	get_header();
	the_post();
	do_action( 'ttbm_single_page_before_wrapper' );
	if ( post_password_required() ) {
		echo get_the_password_form(); // WPCS: XSS ok.
	} else {
		do_action( 'woocommerce_before_single_product' );
        $ttbm_post_id=get_the_id();
        $tour_id=TTBM_Function::post_id_multi_language($ttbm_post_id);
		$template_name = MP_Global_Function::get_post_info( $tour_id, 'ttbm_theme_file', 'default.php' );
		$all_dates                 = TTBM_Function::get_date( $tour_id );
		$travel_type               = TTBM_Function::get_travel_type( $tour_id );
		$tour_type                 = TTBM_Function::get_tour_type( $tour_id );
		$ttbm_display_registration = MP_Global_Function::get_post_info( $tour_id, 'ttbm_display_registration', 'on' );
		$start_price = TTBM_Function::get_tour_start_price( $tour_id );
		$total_seat     = TTBM_Function::get_total_seat( $tour_id );
		$available_seat = TTBM_Function::get_total_available( $tour_id );
		TTBM_Function::update_upcoming_date_month($tour_id,true,$all_dates);
		include_once( TTBM_Function::details_template_path() );
	}
	do_action( 'ttbm_single_page_after_wrapper' );
	get_footer();