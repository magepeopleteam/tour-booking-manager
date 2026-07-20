<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$wp_date_format     = get_option( 'date_format' );
$display_format     = TTBM_Global_Function::wp_date_format_to_moment( $wp_date_format );
if ( ! $display_format ) {
	$display_format = TTBM_Global_Function::date_picker_format();
}
$checkin_ymd        = gmdate( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) ) );
$checkout_ymd       = TTBM_Function::get_hotel_default_checkout_date( $checkin_ymd );
$display_checkin    = date_i18n( $wp_date_format, strtotime( $checkin_ymd ) );
$display_checkout   = date_i18n( $wp_date_format, strtotime( $checkout_ymd ) );
$default_date_range = $display_checkin . '    -    ' . $display_checkout;
?>
<div class="ttbm_registration_area availability_section ttbm_booking_section--hotel ttbm_hotel_details_booking">
	<div class="ttbm_hotel_booking_toolbar ttbm_date_time_select ttbm_hotel_booking_toolbar--inline">
		<div class="ttbm_select_date_area ttbm_hotel_booking_toolbar__inner">
			<h2 class="ttbm_hotel_booking_title">
				<?php esc_html_e( 'Make your booking', 'tour-booking-manager' ); ?>
			</h2>
			<div class="ttbm_hotel_booking_controls booking-button">
				<span class="ttbm_hotel_date_label date_time_label"><?php esc_html_e( 'Select Date Range', 'tour-booking-manager' ); ?></span>
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
				<button class="ttbm_hotel_check_btn ttbm_check_ability ttbm_hotel_room_check_availability" type="button">
					<?php echo esc_html( TTBM_Function::get_translation_settings( 'ttbm_string_check_availability', esc_html__( 'Check Availability', 'tour-booking-manager' ) ) ); ?>
				</button>
			</div>
			<?php wp_nonce_field( 'ttbm_check_availability_action', 'ttbm_nonce' ); ?>
		</div>
	</div>
	<div class="ttbm_booking_panel placeholder_area pRelative"></div>
</div>
