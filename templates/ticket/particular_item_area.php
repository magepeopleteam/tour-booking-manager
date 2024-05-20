<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$travel_type               = $travel_type ?? TTBM_Function::get_travel_type( $tour_id );
	$tour_type                 = $tour_type ?? TTBM_Function::get_tour_type( $tour_id );
	$ttbm_display_registration = $ttbm_display_registration ?? MP_Global_Function::get_post_info( $tour_id, 'ttbm_display_registration', 'on' );
	$all_dates                 = $all_dates ?? TTBM_Function::get_date( $tour_id );
	$particular_dates          = MP_Global_Function::get_post_info( $tour_id, 'ttbm_particular_dates', array() );
	if ( sizeof( $particular_dates ) > 0 && sizeof( $all_dates ) > 0 && $travel_type == 'particular' && $ttbm_display_registration != 'off' ) {
		?>
		<div id="particular_item_area">
			<?php
				if ( $tour_type == 'general' ) {
					foreach ( $all_dates as $date ) {
						$time      = TTBM_Function::get_time( $tour_id, $date );
						$full_date = $date . ' ' . $time;
						?>
						<div class="fdColumn particular_date_area ttbm_registration_area">
							<input type="hidden" name="ttbm_id" value="<?php echo esc_attr( $tour_id ); ?>"/>
							<input type="hidden" name="ttbm_particular_date" value="<?php echo esc_attr( date( 'Y-m-d H:i', strtotime( $full_date ) ) ); ?>"/>
							<div class="particular_date_item">
								<div class="flexColumn">
									<h6><?php echo MP_Global_Function::date_format( $full_date, 'l' ); ?></h6>
									<h6><?php echo MP_Global_Function::date_format( $full_date); ?></h6>
								</div>
								<h4><span class="far fa-arrow-alt-circle-right"></span></h4>
								<?php
									foreach ( $particular_dates as $particular_date ) {
										if ( strtotime( $particular_date['ttbm_particular_start_date'] ) == strtotime( $date ) ) {
											?>
											<div class="flexColumn">
												<h6><?php echo MP_Global_Function::date_format( $particular_date['ttbm_particular_end_date'], 'l' ); ?></h6>
												<h6><?php echo MP_Global_Function::date_format( $particular_date['ttbm_particular_end_date']); ?></h6>
											</div>
											<?php
										}
									}
								?>
								<h4><?php echo wc_price( TTBM_Function::get_tour_start_price( $tour_id, $full_date ) ); ?></h4>
								<button type="button" class="dButton_xs get_particular_ticket">
									<?php esc_html_e( 'Confirm Dates', 'tour-booking-manager' ); ?>
								</button>
							</div>
							<div class="ttbm_booking_panel">
							</div>
						</div>
						<?php
					}
				}
				if ( $tour_type == 'hotel' ) {
					foreach ( $all_dates as $date ) {
						//$time      = TTBM_Function::get_time( $tour_id, $date );
						$full_date = $date;
						?>
						<div class="fdColumn particular_date_area">
							<div class="particular_date_item">
								<div class="flexColumn">
                                    <h6><?php echo MP_Global_Function::date_format( $full_date, 'l' ); ?></h6>
                                    <h6><?php echo MP_Global_Function::date_format( $full_date); ?></h6>
								</div>
								<h4><span class="far fa-arrow-alt-circle-right"></span></h4>
								<?php
									foreach ( $particular_dates as $particular_date ) {
										if ( strtotime( $particular_date['ttbm_particular_start_date'] ) == strtotime( $date ) ) {
											?>
											<input type="hidden" name="ttbm_hotel_date_range" value="<?php echo esc_attr( date( 'Y/m/d', strtotime( $date ) ) ) . '    -     ' . esc_attr( date( 'Y/m/d', strtotime( $particular_date['ttbm_particular_end_date'] ) ) ); ?>"/>
											<div class="flexColumn">
												<h6><?php echo MP_Global_Function::date_format( $particular_date['ttbm_particular_end_date'], 'l' ); ?></h6>
												<h6><?php echo MP_Global_Function::date_format( $particular_date['ttbm_particular_end_date'] ); ?></h6>
											</div>
											<?php
										}
									}
								?>
								<h4><?php echo wc_price( TTBM_Function::get_tour_start_price( $tour_id ) ); ?></h4>
								<button type="button" class="dButton_xs get_particular_hotel">
									<?php esc_html_e( 'Confirm Dates', 'tour-booking-manager' ); ?>
								</button>
							</div>
							<?php include( TTBM_Function::template_path( 'ticket/hotel_default_selection.php' ) ); ?>
						</div>
						<?php
					}
				}
			?>
		</div>
		<?php
	}
?>
