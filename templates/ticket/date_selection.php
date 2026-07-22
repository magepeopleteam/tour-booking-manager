<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$travel_type   = $travel_type ?? TTBM_Function::get_travel_type( $tour_id );
	$tour_type     = $tour_type ?? TTBM_Function::get_tour_type( $tour_id );
	$all_dates     = $all_dates ?? TTBM_Function::get_date( $tour_id );
	$check_ability = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_ticketing_system', 'availability_section' );
	if ( sizeof( $all_dates ) > 0 && $travel_type == 'fixed' ) {
		$start_date = $all_dates['date'];
		$end_date   = $all_dates['checkout_date'];
		$start_time = TTBM_Function::normalize_time_value( TTBM_Function::get_time( $tour_id, $start_date ) );
		$start_date_time = $start_time ? $start_date . ' ' . $start_time : $start_date;
		$end_time = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_travel_end_time' );
		$end_date_time = $end_time ? $end_date . ' ' . $end_time : $end_date;
		?>
		<div class="allCenter ttbm_date_time_select">
			<div class="justifyCenter ttbm_select_date_area">
				<h5 class="textWhite">
					<?php
						echo esc_html( TTBM_Function::get_name() ) . '&nbsp;'
						. esc_html__( 'Date : ', 'tour-booking-manager' ) . '&nbsp;' 
						. esc_html( TTBM_Global_Function::date_format( $start_date_time, 'full' ) );

						if ( array_key_exists( 'checkout_date', $all_dates ) && $all_dates['checkout_date'] ) {
							echo '&nbsp;' . esc_html__( '-', 'tour-booking-manager' ) . '&nbsp;' 
							. esc_html( TTBM_Global_Function::date_format( $end_date_time, 'full' ) );
						}
						if ( $tour_type == 'hotel' && $start_date && $end_date ) {
							$hidden_start_date_time = $start_time ? $start_date . ' ' . $start_time : $start_date;
							$hidden_end_date_time = $end_time ? $end_date . ' ' . $end_time : $end_date;
							?>
							<input type="hidden" name="ttbm_hotel_date_range" value="<?php echo esc_attr( gmdate( 'Y/m/d H:i', strtotime( $hidden_start_date_time ) ) ) . '    -     ' . esc_attr( gmdate( 'Y/m/d H:i', strtotime( $hidden_end_date_time ) ) ); ?>"/>
							<?php
						}
					?>
				</h5>
			</div>
		</div>
		<?php
	}
	if ( sizeof( $all_dates ) > 0 && $tour_type == 'hotel' && $travel_type == 'repeated' ) {
		$checkin_ymd         = TTBM_Function::get_hotel_default_checkin_date( $tour_id, $all_dates );
		$checkout_ymd        = TTBM_Function::get_hotel_default_checkout_date( $checkin_ymd );
		$wp_date_format      = get_option( 'date_format' );
		$display_format      = TTBM_Global_Function::wp_date_format_to_moment( $wp_date_format );
		if ( ! $display_format ) {
			$display_format = TTBM_Global_Function::date_picker_format();
		}
		$display_checkin     = date_i18n( $wp_date_format, strtotime( $checkin_ymd ) );
		$display_checkout    = date_i18n( $wp_date_format, strtotime( $checkout_ymd ) );
		$default_date_range  = $display_checkin . '    -    ' . $display_checkout;
		?>
		<div class="ttbm_hotel_booking_toolbar ttbm_date_time_select mB">
			<div class="ttbm_select_date_area">
				<h4 class="ttbm_hotel_booking_title">
					<?php esc_html_e( 'Make your booking', 'tour-booking-manager' ); ?>
				</h4>
				<div class="ttbm_hotel_booking_controls booking-button">
					<div class="ttbm_hotel_date_field date-picker">
						<span class="ttbm_hotel_date_label date_time_label"><?php esc_html_e( 'Select Date Range', 'tour-booking-manager' ); ?></span>
						<div class="ttbm_hotel_date_row">
							<span class="ttbm_hotel_date_input_wrap date-picker-icon">
								<i class="mi mi-calendar-days" aria-hidden="true"></i>
								<input
									type="text"
									name="ttbm_hotel_date_range"
									class="formControl ttbm_hotel_date_input"
									value="<?php echo esc_attr( $default_date_range ); ?>"
									placeholder="<?php echo esc_attr__( 'Checkin - Checkout', 'tour-booking-manager' ); ?>"
									autocomplete="off"
									readonly="readonly"
									data-checkin="<?php echo esc_attr( $checkin_ymd ); ?>"
									data-checkout="<?php echo esc_attr( $checkout_ymd ); ?>"
									data-display-format="<?php echo esc_attr( $display_format ); ?>"
								/>
							</span>
							<button class="ttbm_hotel_check_btn ttbm_check_ability ttbm_hotel_check_availability" type="button">
								<?php echo esc_html( TTBM_Function::get_translation_settings( 'ttbm_string_check_availability', esc_html__( 'Check Availability', 'tour-booking-manager' ) ) ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
