<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$ttbm_post_id  = $ttbm_post_id ?? get_the_id();
$tour_id       = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );

// --- Price ---
// Show whenever there's a real, computed price (ticket type and/or manual
// starting price) — deliberately not gated by the separate
// 'ttbm_display_price_start' toggle. That toggle defaults 'off' for several
// tours on this install (an import artifact, not a deliberate "hide the
// price" choice) and was hiding a real, valid ticket-type price on cards
// that have every reason to show one.
$start_price   = TTBM_Function::get_tour_start_price( $tour_id );

// First ticket type's label (e.g. "Adult"), shown as a "/ Adult" unit suffix after the price.
$price_unit_label = '';
$ticket_types      = TTBM_Function::get_ticket_type( $tour_id );
if ( ! empty( $ticket_types ) && ! empty( $ticket_types[0]['ticket_type_name'] ) ) {
	$price_unit_label = $ticket_types[0]['ticket_type_name'];
}

// --- Duration label (e.g. "7 DAYS / 6 NIGHTS") ---
$duration      = TTBM_Function::get_duration( $ttbm_post_id );
$night         = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_travel_duration_night' );
$duration_type = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_travel_duration_type', 'day' );
$show_duration = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_duration', 'on' ) !== 'off';
$tour_type     = TTBM_Function::get_tour_type( $ttbm_post_id );

$duration_label = '';
if ( $show_duration && ( $duration || $night ) && $tour_type === 'general' ) {
	if ( $duration ) {
		$duration_label .= esc_html( $duration ) . ' ';
		if ( $duration_type === 'day' ) {
			$duration_label .= $duration > 1
				? esc_html__( 'DAYS', 'tour-booking-manager' )
				: esc_html__( 'DAY', 'tour-booking-manager' );
		} elseif ( $duration_type === 'min' ) {
			$duration_label .= $duration > 1
				? esc_html__( 'MINUTES', 'tour-booking-manager' )
				: esc_html__( 'MINUTE', 'tour-booking-manager' );
		} else {
			$duration_label .= $duration > 1
				? esc_html__( 'HOURS', 'tour-booking-manager' )
				: esc_html__( 'HOUR', 'tour-booking-manager' );
		}
	}
	if ( TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_duration_night', 'off' ) !== 'off' && $night ) {
		$duration_label .= ' / ' . esc_html( $night ) . ' ';
		$duration_label .= $night > 1
			? esc_html__( 'NIGHTS', 'tour-booking-manager' )
			: esc_html__( 'NIGHT', 'tour-booking-manager' );
	}
}
?>
<div class="ttbm-gc-footer" >

	<div class="ttbm-gc-price-block" data-placeholder>
		<?php if ( $duration_label ) : ?>
			<span class="ttbm-gc-duration-label"><?php echo wp_kses_post( $duration_label ); ?></span>
		<?php endif; ?>

		<?php if ( $start_price !== '' && (float) $start_price > 0 ) : ?>
			<div class="ttbm-gc-price-row">
				<span class="ttbm-gc-price-from"><?php esc_html_e( 'From', 'tour-booking-manager' ); ?></span>
				<span class="ttbm-gc-price-current">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo wc_price( $start_price );
					?>
				</span>
				<?php if ( $price_unit_label ) : ?>
					<span class="ttbm-gc-price-unit">/<?php echo esc_html( $price_unit_label ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<button type="button" class="ttbm_explore_button" data-href="<?php echo esc_url( get_the_permalink( $ttbm_post_id ) ); ?>" data-placeholder>
		<?php esc_html_e( 'View', 'tour-booking-manager' ); ?>
		<span class="ttbm-gc-view-arrow" aria-hidden="true">&rarr;</span>
	</button>

</div>
