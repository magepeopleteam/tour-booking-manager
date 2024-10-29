<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$ttbm_display_registration = $ttbm_display_registration ?? MP_Global_Function::get_post_info( $tour_id, 'ttbm_display_registration', 'on' );
	if ( $ttbm_display_registration != 'off' ) {
		$all_dates   = $all_dates ?? TTBM_Function::get_date( $tour_id );
		$tour_type   = $tour_type ?? TTBM_Function::get_tour_type( $tour_id );
		$travel_type = $travel_type ?? TTBM_Function::get_travel_type( $tour_id );
		include( TTBM_Function::template_path( 'ticket/date_selection.php' ) );
		
		include( TTBM_Function::template_path( 'ticket/tour_default_selection.php' ) );
		//include( TTBM_Function::template_path( 'ticket/particular_item_area.php' ) );
		if ( sizeof( $all_dates ) > 0 && $tour_type == 'hotel' && $travel_type != 'particular' ) {
			include( TTBM_Function::template_path( 'ticket/hotel_default_selection.php' ) );
		}
		if ( sizeof( $all_dates ) < 1 ) {
			?>
			<div class="dLayout allCenter bgWarning">
				<h3 class="textWhite"><?php esc_html_e( 'Date Expired ! ', 'tour-booking-manager' ) ?></h3>
			</div>
			<?php
		}
	}
?>