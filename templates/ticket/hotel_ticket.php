<?php
// FIXED: Removed leading tab before PHP opening tag - 2025-01-21 by Shahnur Alam
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$tour_date  = $tour_date ?? TTBM_Function::get_date( $tour_id )[0];
	$hotel_id   = $hotel_id ?? current( TTBM_Function::get_hotel_list( $tour_id ) );
//	$room_lists = TTBM_Global_Function::get_post_info( $hotel_id, 'ttbm_room_details', array() );
	$date_range ='';
	if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_frontend_nonce')) {
		$date_range = isset( $_REQUEST['date_range'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['date_range'] ) ) : '';
	}
//    $hotel_booking_data = TTBM_Global_Function::get_hotel_bookings($hotel_id, $check_in, $check_out);
    $hotel_date = explode( "-", $date_range );
    $date1      = gmdate( 'Y-m-d', strtotime( $hotel_date[0] ) );
    $date2      = gmdate( 'Y-m-d', strtotime( $hotel_date[1] ) );
    $days       = date_diff( date_create( $date1 ), date_create( $date2 ) );
    $room_lists = TTBM_Global_Function::ttbm_get_full_room_ticket_info( $hotel_id, $date1, $date2 );

	if ( sizeof( $room_lists ) > 0 && $hotel_id && $date_range ) {

		?>
		<input type="hidden" name='ttbm_tour_hotel_list' value='<?php echo esc_attr( $hotel_id ); ?>'>
		<input type="hidden" name='ttbm_hotel_num_of_day' value='<?php echo esc_attr( $days->days ); ?>'>
		<input type="hidden" name='ttbm_checkin_date' value='<?php echo esc_attr( $date1 ); ?>'>
		<input type="hidden" name='ttbm_checkout_date' value='<?php echo esc_attr( $date2 ); ?>'>
		<div class="ttbm_hotel_room_panel ttbm_default_widget mT">
			<h5 class="ttbm_hotel_room_panel__title">
				<?php TTBM_Function::translation_settings( 'ttbm_string_availabe_ticket_list', esc_html__( 'Available Room List', 'tour-booking-manager' ) ); ?>
			</h5>
			<div class="ttbm_hotel_room_table_wrap ttbm_widget_content" data-placeholder>
				<table class="mp_tour_ticket_type ttbm_hotel_room_table">
					<thead>
					<tr>
						<th class="ttbm_hotel_room_col--name"><?php echo esc_html( TTBM_Function::ticket_name_text() ); ?></th>
						<th class="ttbm_hotel_room_col--price"><?php echo esc_html( TTBM_Function::ticket_price_text() ); ?></th>
						<th class="ttbm_hotel_room_col--qty"><?php echo esc_html( TTBM_Function::ticket_qty_text() ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
						foreach ( $room_lists as $ticket ) {
							$room_name        = array_key_exists( 'ttbm_hotel_room_name', $ticket ) ? $ticket['ttbm_hotel_room_name'] : '';
							$price            = array_key_exists( 'ttbm_hotel_room_price', $ticket ) ? $ticket['ttbm_hotel_room_price'] : 0;
							$sale_price       = array_key_exists( 'sale_price', $ticket ) ? $ticket['sale_price'] : '';
							$price            = TTBM_Global_Function::wc_price( $tour_id, $price );
							$ticket_price_raw = TTBM_Global_Function::price_convert_raw( $price );
							$ticket_qty       = array_key_exists( 'ttbm_hotel_room_qty', $ticket ) ? $ticket['ttbm_hotel_room_qty'] : 0;
							$reserve          = 0;
							$min_qty          = apply_filters( 'ttbm_ticket_type_min_qty', 0 );
							$max_qty          = apply_filters( 'ttbm_ticket_type_max_qty', 0 );
							$sold_type        = TTBM_Function::get_total_sold( $tour_id, $tour_date, $room_name, $hotel_id );
							$available        = $ticket['available'] + $reserve;
							$adult_qty        = array_key_exists( 'ttbm_hotel_room_capacity_adult', $ticket ) ? $ticket['ttbm_hotel_room_capacity_adult'] : 0;
							$child_qty        = array_key_exists( 'ttbm_hotel_room_capacity_child', $ticket ) ? $ticket['ttbm_hotel_room_capacity_child'] : 0;

							?>
							<tr class="ttbm_hotel_room_row">
								<td class="ttbm_hotel_room_col--name" data-label="<?php echo esc_attr( TTBM_Function::ticket_name_text() ); ?>">
									<div class="ttbm_hotel_room_name"><?php echo esc_html( $room_name ); ?></div>
									<?php if ( $adult_qty > 0 || $child_qty > 0 || $available > 0 ) { ?>
										<div class="ttbm_hotel_room_meta">
											<?php if ( $adult_qty > 0 ) { ?>
												<span class="ttbm_hotel_room_badge" title="<?php esc_attr_e( 'Adults', 'tour-booking-manager' ); ?>">
													<i class="fas fa-user-alt" aria-hidden="true"></i>
													<?php echo esc_html( $adult_qty ); ?>
												</span>
											<?php } ?>
											<?php if ( $child_qty > 0 ) { ?>
												<span class="ttbm_hotel_room_badge" title="<?php esc_attr_e( 'Children', 'tour-booking-manager' ); ?>">
													<i class="fas fa-child-dress" aria-hidden="true"></i>
													<?php echo esc_html( $child_qty ); ?>
												</span>
											<?php } ?>
											<?php if ( $available > 0 ) { ?>
												<span class="ttbm_hotel_room_badge ttbm_hotel_room_badge--stock" title="<?php esc_attr_e( 'Available', 'tour-booking-manager' ); ?>">
													<i class="fas fa-circle" aria-hidden="true"></i>
													<?php echo esc_html( $available ); ?>
												</span>
											<?php } ?>
										</div>
									<?php } ?>
								</td>
								<td class="ttbm_hotel_room_col--price" data-label="<?php echo esc_attr( TTBM_Function::ticket_price_text() ); ?>">
									<?php if ( $sale_price ) { ?>
										<span class="ttbm_hotel_room_sale strikeLine"><?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo wc_price( $tour_id, $sale_price );
										?></span>
									<?php } ?>
									<span class="ttbm_hotel_room_price"><?php echo wp_kses_post( $price ); ?></span>
									<span class="ttbm_hotel_room_price_meta">
										/ <?php esc_html_e( 'Night', 'tour-booking-manager' ); ?> &times; <?php echo esc_html( $days->days ); ?>
									</span>
								</td>
								<td class="ttbm_hotel_room_col--qty" data-label="<?php echo esc_attr( TTBM_Function::ticket_qty_text() ); ?>">
									<?php TTBM_Layout::qty_input( $room_name, $available, 'inputbox', 0, $min_qty, $max_qty, $ticket_price_raw, 'ticket_qty[]' ); ?>
								</td>
							</tr>
							<tr class="ttbm_hotel_room_hidden_inputs">
								<td colspan="3">
									<input type="hidden" name='tour_id[]' value='<?php echo esc_html( $tour_id ); ?>'>
									<input type="hidden" name='ticket_name[]' value='<?php echo esc_html( $room_name ); ?>'>
									<input type="hidden" name='ticket_max_qty[]' value='<?php echo esc_html( $max_qty ); ?>'>
									<?php do_action( 'ttbm_after_ticket_type_item', $tour_id, $ticket ); ?>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
		include( TTBM_Function::template_path( 'ticket/extra_service.php' ) );
		do_action( 'ttbm_book_now_before', $tour_id );
		include( TTBM_Function::template_path( 'ticket/book_now.php' ) );
	} else {
		?>
		<div class="dLayout allCenter _mT_bgWarning" data-placeholder>
			<h3 class="textWhite"><?php esc_html_e( 'No Room available !', 'tour-booking-manager' ); ?></h3>
		</div>
		<?php
	}
?>