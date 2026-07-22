<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$ttbm_post_id  = $ttbm_post_id ?? get_the_id();
$tour_id       = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );

// --- Price ---
$start_price   = TTBM_Function::get_tour_start_price( $ttbm_post_id );
$show_price    = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_price_start', 'on' ) !== 'off';

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

		<?php if ( $start_price && $show_price ) : ?>
			<div class="ttbm-gc-price-row">
				<span class="ttbm-gc-price-current">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo wc_price( $start_price );
					?>
				</span>
			</div>
		<?php endif; ?>
	</div>

	<button type="button" class="ttbm_explore_button" data-href="<?php echo esc_url( get_the_permalink( $ttbm_post_id ) ); ?>" data-placeholder>
		<?php esc_html_e( 'Explore', 'tour-booking-manager' ); ?>
	</button>

</div>
