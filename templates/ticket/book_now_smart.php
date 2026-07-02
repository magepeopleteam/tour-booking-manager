<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$ttbm_post_id      = $ttbm_post_id ?? get_the_id();
$tour_id           = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );
$ttbm_product_id   = TTBM_Global_Function::get_post_info( $tour_id, 'link_wc_product' );
if ( empty( $ttbm_product_id ) && ! TTBM_Global_Function::has_woocommerce() && apply_filters( 'ttbm_booking_available', false, $tour_id ) ) {
	// No hidden WC product (WooCommerce was never active) but a non-WC checkout
	// (Pro custom payment) is available — it resolves the tour from this value.
	$ttbm_product_id = $tour_id;
}
if ( empty( $ttbm_product_id ) ) {
	return;
}
if ( TTBM_Payment_Settings::login_required_for_booking() && ! is_user_logged_in() ) {
	TTBM_Payment_Settings::render_login_prompt();
	return;
}
$seat_infos        = TTBM_Global_Function::get_post_info( $tour_id, 'ttbma_seat_plan', array() );
$display           = TTBM_Global_Function::get_post_info( $tour_id, 'ttbma_display_seat_plan', 'off' );
$display_front_end = TTBM_Global_Function::get_post_info( $tour_id, 'frontend_display_seat_plan', 'on' );
$seat_plan         = class_exists( 'TTBMA_Seat_Plan' ) && $display === 'on' && sizeof( $seat_infos ) > 0 && $display_front_end === 'on' ? 'dNone' : '';
$button_type       = apply_filters( 'ttbm_book_now_button_type', 'button', $tour_id );
?>
<div class="ttbm_book_now_area ttbm_smart_book_now_area" title="<?php esc_attr_e( 'Select Date First', 'tour-booking-manager' ); ?>" data-placeholder>
	<div class="ttbm_order_summary ttbm_smart_order_summary" aria-hidden="true">
		<div class="ttbm_summary_values">
			<div class="ttbm_summary_item">
				<b class="tour_qty">0</b>
				<span class="ttbm_summary_label"><?php esc_html_e( 'Tickets', 'tour-booking-manager' ); ?></span>
			</div>
			<div class="ttbm_summary_item">
				<b class="tour_price"></b>
				<span class="ttbm_summary_label"><?php esc_html_e( 'Total', 'tour-booking-manager' ); ?></span>
			</div>
		</div>
	</div>
	<?php do_action( 'ttbm_before_add_cart_btn', $ttbm_product_id, $tour_id ); ?>
	<?php if ( class_exists( 'TTBMA_Seat_Plan' ) && $display === 'on' && sizeof( $seat_infos ) > 0 && $display_front_end === 'on' ) { ?>
		<button class="dButton ttbm_load_seat_plan" type="submit">
			<?php esc_html_e( 'Seat Plan', 'tour-booking-manager' ); ?>
		</button>
	<?php } ?>
	<button class="ttbm_smart_book_trip_btn ttbm_book_now <?php echo esc_attr( $seat_plan ); ?>" type="<?php echo esc_attr( $button_type ); ?>">
		<?php esc_html_e( 'Book This Trip', 'tour-booking-manager' ); ?>
	</button>
	<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $ttbm_product_id ); ?>" class="dNone ttbm_add_to_cart">
		<?php esc_html_e( 'Book Now', 'tour-booking-manager' ); ?>
	</button>
	<ul class="ttbm_smart_trust_signals">
		<li class="ttbm_smart_trust_item">
			<span class="ttbm_smart_trust_icon" aria-hidden="true"><i class="far fa-check-circle"></i></span>
			<span class="ttbm_smart_trust_text"><?php esc_html_e( 'Instant confirmation', 'tour-booking-manager' ); ?></span>
		</li>
		<li class="ttbm_smart_trust_item">
			<span class="ttbm_smart_trust_icon" aria-hidden="true"><i class="fas fa-lock"></i></span>
			<span class="ttbm_smart_trust_text"><?php esc_html_e( 'Secure payment', 'tour-booking-manager' ); ?></span>
		</li>
		<li class="ttbm_smart_trust_item">
			<span class="ttbm_smart_trust_icon" aria-hidden="true"><i class="far fa-calendar-times"></i></span>
			<span class="ttbm_smart_trust_text" title="<?php echo esc_attr( TTBM_Function::cancellation_policy_text() ); ?>">
				<?php esc_html_e( 'Free cancellation', 'tour-booking-manager' ); ?>
			</span>
		</li>
	</ul>
</div>
