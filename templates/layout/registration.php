<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	if ( MP_Global_Function::get_post_info( $tour_id, 'ttbm_display_registration', 'on' ) != 'off' ) {
		$all_dates = TTBM_Function::get_date( $tour_id );
		if ( sizeof( $all_dates ) > 0 ) {
			$date          = current( $all_dates );
			$time          = TTBM_Function::get_time( $tour_id, $date );
			$time          = is_array( $time ) ? $time[0]['time'] : $time;
			$date          = $time ? $date . ' ' . $time : $date;
			$date=$time?date( 'Y-m-d H:i', strtotime( $date) ):date( 'Y-m-d', strtotime( $date) );
			$check_ability = MP_Global_Function::get_post_info( $tour_id, 'ttbm_ticketing_system', 'availability_section' );
			$travel_type   = TTBM_Function::get_travel_type( $tour_id );
			$template_name = MP_Global_Function::get_post_info( $tour_id, 'ttbm_theme_file', 'default.php' );
			?>
			<div class="ttbm_registration_area <?php echo esc_attr( $check_ability ); ?>">
				<input type="hidden" name="ttbm_id" value="<?php echo esc_attr( $tour_id ); ?>"/>
				<input type="hidden" name="ttbm_date" value="<?php echo esc_attr( date( 'Y-m-d', strtotime( $date) ) ); ?>"/>
				<?php do_action( 'ttbm_date_time_select', $tour_id, $all_dates ); ?>

				<?php do_action( 'ttbm_hotel_select', $tour_id, $check_ability ); ?>

				<?php
					if ( $template_name == 'viator.php' ) {
						TTBM_Layout::availability_button( $tour_id );
					}
				?>
				<div class="ttbm_booking_panel placeholder_area">
					<?php if ( $check_ability == 'regular_ticket' || $travel_type == 'fixed' ) { ?>
						<?php do_action( 'ttbm_booking_panel', $tour_id, $date, '', $check_ability); ?>
					<?php } ?>
				</div>
			</div>
			<?php
		} else {
			?>
			<div class="dLayout allCenter bgWarning">
				<h3 class="textWhite"><?php esc_html_e( 'Date Expired ! ', 'tour-booking-manager' ) ?></h3>
			</div>
			<?php
		}
	}
?>