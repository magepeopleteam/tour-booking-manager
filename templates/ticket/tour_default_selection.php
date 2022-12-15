<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$tour_id       = $tour_id ?? get_the_id();
	$all_dates     = $all_dates ?? TTBM_Function::get_date( $tour_id );
	$travel_type   = $travel_type ?? TTBM_Function::get_travel_type( $tour_id );
	$tour_type     = $tour_type ?? TTBM_Function::get_tour_type( $tour_id );
	$template_name = $template_name ?? TTBM_Function::get_post_info( $tour_id, 'ttbm_theme_file', 'default.php' );
	if ( sizeof( $all_dates ) > 0 && $tour_type == 'general' && $travel_type != 'particular' ) {
		$date          = current( $all_dates );
		$check_ability = TTBM_Function::get_post_info( $tour_id, 'ttbm_ticketing_system', 'availability_section' );
		$time          = TTBM_Function::get_time( $tour_id, $date );
		$time          = is_array( $time ) ? $time[0]['time'] : $time;
		$date          = $time ? $date . ' ' . $time : $date;
		$date=$time?date( 'Y-m-d H:i', strtotime( $date) ):date( 'Y-m-d', strtotime( $date) );
		$date_format   = $date_format??TTBM_Function::date_format();
		?>
		<div class="ttbm_registration_area <?php echo esc_attr( $check_ability ); ?>">
			<input type="hidden" name="ttbm_id" value="<?php echo esc_attr( $tour_id ); ?>"/>
			<input type="hidden" name="ttbm_date" value="<?php echo esc_attr( $date ); ?>"/>
			<?php
				if ($travel_type == 'repeated' ) {
					$time_slots = TTBM_Function::get_time( $tour_id, $all_dates[0] );
					?>
					<div class="allCenter ttbm_date_time_select">
						<div class="justifyCenter ttbm_select_date_area">
							<label>
								<span class="date_time_label"><?php echo is_array( $time_slots ) && sizeof( $time_slots ) > 0 ? esc_html__( 'Select Date & Time : ', 'tour-booking-manager' ) : esc_html__( 'Select Date  : ', 'tour-booking-manager' ); ?></span>
								<input type="text" id="ttbm_select_date" name="" class="formControl date_type" value="<?php echo esc_attr__( date_i18n( $date_format, strtotime( $all_dates[0] ) ) ); ?>"/>
							</label>
							<?php
								$template_name = TTBM_Function::get_post_info( $tour_id, 'ttbm_theme_file', 'default.php' );
								if ( $template_name != 'viator.php' && $check_ability == 'availability_section' ) {
									TTBM_Layout::availability_button( $tour_id );
								}
							?>
							<?php if ( is_array( $time_slots ) && sizeof( $time_slots ) > 0 && $check_ability == 'regular_ticket' && $template_name != 'viator.php' ) { ?>
								<div class="flexWrap ttbm_select_time_area">
									<?php do_action( 'ttbm_time_select', $tour_id, $all_dates[0] ); ?>
								</div>
							<?php } ?>
						</div>
						<?php if ( is_array( $time_slots ) && sizeof( $time_slots ) > 0 && ( $template_name == 'viator.php' || $check_ability == 'availability_section' ) ) { ?>
							<div class="flexWrap ttbm_select_time_area">
								<?php if ( $check_ability == 'regular_ticket' ) { ?>
									<?php do_action( 'ttbm_time_select', $tour_id, $all_dates[0] ); ?>
								<?php } ?>
							</div>
						<?php } ?>
					</div>
					<?php
					do_action( 'ttbm_date_picker_js', '#ttbm_select_date', $all_dates );
				}
				if ( $template_name == 'viator.php' ) {
					TTBM_Layout::availability_button( $tour_id );
				}
			?>
			<div class="ttbm_booking_panel placeholder_area">
				<?php if ( $check_ability == 'regular_ticket' || $travel_type == 'fixed' ) { ?>
					<?php do_action( 'ttbm_booking_panel', $tour_id, $date, '', $check_ability ); ?>
				<?php } ?>
			</div>
		</div>
	<?php } ?>