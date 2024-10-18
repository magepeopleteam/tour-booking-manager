<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$travel_type   = $travel_type ?? TTBM_Function::get_travel_type( $tour_id );
	$tour_type     = $tour_type ?? TTBM_Function::get_tour_type( $tour_id );
	$all_dates     = $all_dates ?? TTBM_Function::get_date( $tour_id );
	$check_ability = MP_Global_Function::get_post_info( $tour_id, 'ttbm_ticketing_system', 'availability_section' );
	if ( sizeof( $all_dates ) > 0 && $travel_type == 'fixed' ) {
		$start_date = $all_dates['date'];
		$end_date   = $all_dates['checkout_date'];
		?>
		<div class="allCenter ttbm_date_time_select">
			<div class="justifyCenter ttbm_select_date_area">
				<h5 class="textWhite">
					<?php
						echo TTBM_Function::get_name() . '&nbsp;' . esc_html__( 'Date : ', 'tour-booking-manager' ) . '&nbsp;' .MP_Global_Function::date_format( $start_date );
						if ( array_key_exists( 'checkout_date', $all_dates ) && $all_dates['checkout_date'] ) {
							echo '&nbsp;' . esc_html__( '-', 'tour-booking-manager' ) . '&nbsp;' . MP_Global_Function::date_format( $end_date );
						}
						if ( $tour_type == 'hotel' && $start_date && $end_date ) {
							?>
							<input type="hidden" name="ttbm_hotel_date_range" value="<?php echo esc_attr( date( 'Y/m/d', strtotime( $start_date ) ) ) . '    -     ' . esc_attr( date( 'Y/m/d', strtotime( $end_date ) ) ); ?>"/>
							<?php
						}
					?>
				</h5>
			</div>
		</div>
		<?php
	}
	if ( sizeof( $all_dates ) > 0 && $tour_type == 'hotel' && $travel_type == 'repeated' ) {
		?>
		<div class="justifyBetween ttbm_date_time_select mB">
			<div class="justifyBetween ttbm_select_date_area">
				<h4 class="ttbm_title_style_2">
					<?php esc_html_e('Make your booking', 'tour-booking-manager'); ?>
				</h4>
				<div class="dFlex justifyBetween booking-button">
					<label class="_allCenter">
						<span class="date_time_label mR_xs"><?php esc_html_e( 'Select Date Range : ', 'tour-booking-manager' ); ?></span>
						<input type="text" name="ttbm_hotel_date_range" class="formControl " value="" placeholder="<?php echo esc_html__( 'Checkin - Checkout', 'tour-booking-manager' ); ?>"/>
					</label>
					<button class="navy_blueButton ttbm_check_ability ttbm_hotel_check_availability" type="button">
						<?php esc_html_e( 'Check  Availability', 'tour-booking-manager' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}
