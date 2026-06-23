<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$ttbm_post_id = $ttbm_post_id ?? get_the_id();
$tour_id      = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );
$tour_date    = $tour_date ?? current( TTBM_Function::get_date( $tour_id ) );
$ticket_lists = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_ticket_type', array() );
$available_seat = TTBM_Function::get_total_available( $tour_id, $tour_date );
$availability_info = TTBM_Function::get_ticket_availability_info( $tour_id, $tour_date );

if ( ! is_array( $ticket_lists ) || empty( $ticket_lists ) ) {
	?>
	<div class="dLayout allCenter bgWarning">
		<h3 class="textWhite"><?php esc_html_e( 'No Ticket Available ! ', 'tour-booking-manager' ); ?></h3>
	</div>
	<?php
	return;
}

do_action( 'ttbm_before_ticket_type_area', $tour_id, $tour_date );
?>
<div class="ttbm_ticket_area ttbm_enhanced_ticket_area ttbm_smart_ticket_area">
	<span class="ttbm_last_updated" data-tour-id="<?php echo esc_attr( $tour_id ); ?>" data-tour-date="<?php echo esc_attr( $tour_date ); ?>" style="display: none;"></span>
	<span id="ttbm_total_available" style="display: none;"><?php echo esc_html( $available_seat ); ?></span>

	<div class="ttbm_smart_ticket_list">
		<?php
		foreach ( $ticket_lists as $index => $ticket ) {
			$ticket_name       = $ticket['ticket_type_name'] ?? '';
			$price             = TTBM_Function::get_price_by_name( $ticket_name, $tour_id, '', '', $tour_date );
			$regular_price     = TTBM_Function::check_discount_price_exit( $tour_id, $ticket_name, '', '', $tour_date );
			$ticket_price      = TTBM_Global_Function::wc_price( $tour_id, $price );
			$ticket_price_raw  = TTBM_Global_Function::price_convert_raw( $ticket_price );
			$ticket_qty        = ( $ticket['ticket_type_qty'] ?? 0 ) > 0 ? $ticket['ticket_type_qty'] : 0;
			$ticket_qty        = apply_filters( 'ttbm_ticket_capacity', intval( $ticket_qty ), $tour_id, $tour_date, $ticket_name );
			$reserve           = ( $ticket['ticket_type_resv_qty'] ?? 0 ) > 0 ? $ticket['ticket_type_resv_qty'] : 0;
			$ticket_qty_type   = $ticket['ticket_type_qty_type'] ?? 'inputbox';
			$default_qty       = ( $ticket['ticket_type_default_qty'] ?? 0 ) > 0 ? $ticket['ticket_type_default_qty'] : 0;
			$min_qty           = apply_filters( 'ttbm_ticket_type_min_qty', 0, $tour_id, $ticket );
			$max_qty           = apply_filters( 'ttbm_ticket_type_max_qty', 0, $tour_id, $ticket );
			$sold_type         = TTBM_Function::get_total_sold( $tour_id, $tour_date, $ticket_name );
			$ticket_info       = $availability_info[ $ticket_name ] ?? array();
			$total_capacity    = $ticket_info['total_capacity'] ?? $ticket_qty;
			$sold_qty          = $ticket_info['sold_qty'] ?? $sold_type;
			$stock_status      = $ticket_info['stock_status'] ?? 'in_stock';
			$sold_type         = apply_filters( 'ttbm_sold_qty', $sold_type, $tour_id, $tour_date, $ticket_name );
			$available         = isset( $ticket_info['available_qty'] ) ? $ticket_info['available_qty'] : ( (int) $ticket_qty - ( $sold_type + (int) $reserve ) );
			$available         = apply_filters( 'ttbm_group_ticket_qty', $available, $tour_id, $ticket_name, $tour_date );
			$available         = max( 0, floor( $available ) );
			$description       = $ticket['ticket_type_description'] ?? '';
			$is_sold_out       = $available <= 0;
			$stock_status      = $is_sold_out ? 'sold_out' : 'in_stock';
			$discount_pct      = $regular_price ? round( ( ( $regular_price - $price ) / $regular_price ) * 100 ) : 0;
			$person_label      = preg_match( '/child|kid|infant/i', $ticket_name ) ? __( 'child', 'tour-booking-manager' ) : __( 'person', 'tour-booking-manager' );
			?>
			<div class="ttbm_smart_ticket_card ttbm_stock_<?php echo esc_attr( $stock_status ); ?>"
				data-ticket-name="<?php echo esc_attr( $ticket_name ); ?>"
				data-shared-capacity-enabled="<?php echo ! empty( $ticket_info['shared_capacity_enabled'] ) ? '1' : '0'; ?>"
				data-shared-available-qty="<?php echo ! empty( $ticket_info['shared_capacity_enabled'] ) ? esc_attr( $available ) : ''; ?>">
				<div class="ttbm_smart_ticket_card__main">
					<div class="ttbm_smart_ticket_card__info">
						<h4 class="ttbm_smart_ticket_card__name"><?php echo esc_html( $ticket_name ); ?></h4>
						<div class="ttbm_smart_ticket_card__price_row">
							<span class="ttbm_smart_ticket_card__price"><?php echo wp_kses_post( $ticket_price ); ?></span>
							<span class="ttbm_smart_ticket_card__per">/ <?php echo esc_html( $person_label ); ?></span>
						</div>
						<?php if ( $regular_price ) { ?>
							<div class="ttbm_smart_ticket_card__meta">
								<span class="ttbm_regular_price strikeLine"><?php echo wp_kses_post( wc_price( $regular_price ) ); ?></span>
								<?php if ( $discount_pct > 0 ) { ?>
									<span class="ttbm_smart_ticket_card__badge"><?php echo esc_html( $discount_pct . '% off' ); ?></span>
								<?php } ?>
							</div>
						<?php } ?>
						<?php if ( $description ) { ?>
							<div class="ttbm_smart_ticket_card__desc"><?php TTBM_Custom_Layout::load_more_text( $description, 80 ); ?></div>
						<?php } ?>
					</div>
					<div class="ttbm_smart_ticket_card__qty">
						<?php if ( ! $is_sold_out ) { ?>
							<?php TTBM_Layout::qty_input( $ticket_name, $available, $ticket_qty_type, $default_qty, $min_qty, $max_qty, $ticket_price_raw, 'ticket_qty[' . $index . ']', $tour_id ); ?>
						<?php } else { ?>
							<span class="ttbm_smart_ticket_card__sold_out"><?php esc_html_e( 'Sold out', 'tour-booking-manager' ); ?></span>
						<?php } ?>
					</div>
				</div>
				<div class="ttbm_hidden_inputs">
					<?php do_action( 'ttbm_input_data', $ticket_name, $tour_id ); ?>
					<input type="hidden" name="tour_id[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $tour_id ); ?>">
					<input type="hidden" name="ticket_name[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $ticket_name ); ?>">
					<input type="hidden" name="ticket_max_qty[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $max_qty ); ?>">
					<input type="hidden" name="ticket_available_qty[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $available ); ?>">
					<input type="hidden" name="ticket_capacity[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $total_capacity ); ?>">
				</div>
				<div class="ttbm_attendee_form_row">
					<?php do_action( 'ttbm_after_ticket_type_item', $tour_id, $ticket ); ?>
				</div>
			</div>
			<?php
		}
		?>
	</div>

	<?php include TTBM_Function::template_path( 'ticket/extra_service_smart.php' ); ?>
</div>
<?php
do_action( 'ttbm_load_seat_plan', $tour_id, $tour_date );
do_action( 'ttbm_book_now_before', $tour_id );
include TTBM_Function::template_path( 'ticket/book_now_smart.php' );
