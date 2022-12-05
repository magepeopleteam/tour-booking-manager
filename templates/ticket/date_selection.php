<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$tour_id       = $tour_id ?? get_the_id();
	$travel_type   = $travel_type ?? TTBM_Function::get_travel_type( $tour_id );
	$tour_type     = $tour_type ?? TTBM_Function::get_tour_type( $tour_id );
	$all_dates     = $all_dates ?? TTBM_Function::get_date( $tour_id );
	$date_format   = TTBM_Function::date_format();
	$check_ability = TTBM_Function::get_post_info( $tour_id, 'ttbm_ticketing_system', 'availability_section' );
	if ( sizeof( $all_dates ) > 0 && $travel_type == 'fixed' ) {
		$start_date = $all_dates['date'];
		$end_date   = $all_dates['checkout_date'];
		?>
		<div class="allCenter ttbm_date_time_select">
			<div class="justifyCenter ttbm_select_date_area">
				<h5 class="textWhite">
					<?php
						echo TTBM_Function::get_name() . '&nbsp;' . esc_html__( 'Date : ', 'tour-booking-manager' ) . '&nbsp;' . date_i18n( $date_format, strtotime( $start_date ) );
						if ( array_key_exists( 'checkout_date', $all_dates ) && $all_dates['checkout_date'] ) {
							echo '&nbsp;' . esc_html__( '-', 'tour-booking-manager' ) . '&nbsp;' . date_i18n( $date_format, strtotime( $end_date ) );
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
		<div class="allCenter ttbm_date_time_select">
			<div class="allCenter ttbm_select_date_area">
				<label class="col_6 mR" data-placeholder>
					<span class="date_time_label"><?php esc_html_e( 'Select Date Range : ', 'tour-booking-manager' ); ?></span>
					<input type="text" name="ttbm_hotel_date_range" class="formControl textCenter" value="" placeholder="<?php echo esc_html__( 'Checkin - Checkout', 'tour-booking-manager' ); ?>"/>
				</label>
				<button class="_dButton  ttbm_hotel_check_availability" type="button">
					<?php esc_html_e( 'Check  Availability', 'tour-booking-manager' ); ?>
				</button>
			</div>
		</div>
		<?php
	}
