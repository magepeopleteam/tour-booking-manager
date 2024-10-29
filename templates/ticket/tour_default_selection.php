<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$all_dates     = $all_dates ?? TTBM_Function::get_date( $tour_id );
	$travel_type   = $travel_type ?? TTBM_Function::get_travel_type( $tour_id );
	$tour_type     = $tour_type ?? TTBM_Function::get_tour_type( $tour_id );
	$template_name = $template_name ?? MP_Global_Function::get_post_info( $tour_id, 'ttbm_theme_file', 'default.php' );
	if ( sizeof( $all_dates ) > 0 && $tour_type == 'general' && $travel_type != 'particular' ) {
		$date          = current( $all_dates );
		$check_ability = MP_Global_Function::get_post_info( $tour_id, 'ttbm_ticketing_system', 'availability_section' );
		$time          = TTBM_Function::get_time( $tour_id, $date );
		$time          = is_array( $time ) ? $time[0]['time'] : $time;
		$date          = $time ? $date . ' ' . $time : $date;
		$date=$time?date( 'Y-m-d H:i', strtotime( $date) ):date( 'Y-m-d', strtotime( $date) );
        /************/
		$date_format = MP_Global_Function::date_picker_format();
		$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
		$hidden_date = $date ? date('Y-m-d', strtotime($date)) : '';
		$visible_date = $date ? date_i18n($date_format, strtotime($date)) : '';
		?>
		<div class="ttbm_registration_area <?php echo esc_attr( $check_ability ); ?>">
			<input type="hidden" name="ttbm_id" value="<?php echo esc_attr( $tour_id ); ?>"/>
			<?php
				if ($travel_type == 'repeated' ) {
					$time_slots = TTBM_Function::get_time( $tour_id, $all_dates[0] );
					?>
					<div class="allCenter ttbm_date_time_select">
						<div class="justifyBetween ttbm_select_date_area">
							<h4 class="ttbm_title_style_2" data-placeholder>
								<?php esc_html_e('Make your booking', 'tour-booking-manager'); ?>
							</h4>
							<div class="dFlex justifyBetween booking-button">
								<label class="_allCenter">
									<span class="date_time_label _mR_xs"><?php echo is_array( $time_slots ) && sizeof( $time_slots ) > 0 ? esc_html__( 'Select Date & Time : ', 'tour-booking-manager' ) : esc_html__( 'Select Date  : ', 'tour-booking-manager' ); ?></span>
									<span class="date-picker-icon">
									<i class="far fa-calendar-alt"></i>
									<input type="hidden" name="ttbm_date" value="<?php echo esc_attr($hidden_date); ?>" required/>
									<input id="ttbm_select_date" type="text" value="<?php echo esc_attr($visible_date); ?>" class="formControl mb-0 " placeholder="<?php echo esc_attr($now); ?>"  readonly required/>
									</span>
								</label>
								<?php
									$template_name = MP_Global_Function::get_post_info( $tour_id, 'ttbm_theme_file', 'default.php' );
									// if ( $template_name != 'viator.php' && $check_ability == 'availability_section' ) {
										
									// }
									TTBM_Layout::availability_button( $tour_id );
								?>
							</div>
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
					do_action( 'mp_load_date_picker_js', '#ttbm_select_date', $all_dates );
				}
			?>
			<div class="ttbm_booking_panel placeholder_area">
				<?php if ( $check_ability == 'regular_ticket' || $travel_type == 'fixed' ) { ?>
					<?php do_action( 'ttbm_booking_panel', $tour_id, $date, '', $check_ability); ?>
				<?php } ?>
			</div>
		</div>
	<?php } ?>