<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	/**
	 * Note under the Book Now button when the site has no way to take payment yet:
	 * WooCommerce is not active and no payment method (e.g. Offline Payment) is on.
	 * Visitors see why they can't book; administrators also get a link to fix it.
	 */
	$ttbm_payment_settings_url = admin_url( 'edit.php?post_type=ttbm_tour&page=ttbm_settings_page#ttbm_payment_settings' );
?>
<div class="ttbm_booking_note" role="note">
	<span class="ttbm_booking_note__icon" aria-hidden="true">
		<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 11v5M12 8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
	</span>
	<span class="ttbm_booking_note__text">
		<?php esc_html_e( "Online booking isn't open yet. Please check back soon.", 'tour-booking-manager' ); ?>
		<?php if ( current_user_can( 'manage_options' ) ) : ?>
			<span class="ttbm_booking_note__admin">
				<?php esc_html_e( 'Only admins see this:', 'tour-booking-manager' ); ?>
				<a href="<?php echo esc_url( $ttbm_payment_settings_url ); ?>"><?php esc_html_e( 'turn on a payment method', 'tour-booking-manager' ); ?></a>
				<?php esc_html_e( 'or activate WooCommerce to take bookings.', 'tour-booking-manager' ); ?>
			</span>
		<?php endif; ?>
	</span>
</div>
