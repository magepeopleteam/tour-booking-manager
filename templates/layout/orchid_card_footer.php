<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$ttbm_post_id  = $ttbm_post_id ?? get_the_id();
$tour_id       = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );
$start_price   = TTBM_Function::get_tour_start_price( $ttbm_post_id );
$show_price    = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_price_start', 'on' ) !== 'off';
?>
<div class="ttbm-orchid-footer">
	<div class="ttbm-orchid-meta-row">
		<?php if ( $start_price && $show_price ) : ?>
			<div class="ttbm-orchid-price" data-placeholder>
				<span class="ttbm-orchid-from"><?php esc_html_e( 'From', 'tour-booking-manager' ); ?></span>
				<span class="ttbm-orchid-price-value">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo wc_price( $start_price );
					?>
				</span>
			</div>
		<?php else : ?>
			<div class="ttbm-orchid-price"></div>
		<?php endif; ?>
		<div class="ttbm-orchid-duration">
			<?php include TTBM_Function::template_path( 'layout/list_duration.php' ); ?>
		</div>
	</div>
	<button type="button" class="ttbm_explore_button ttbm-orchid-explore" data-href="<?php echo esc_url( get_the_permalink( $ttbm_post_id ) ); ?>" data-placeholder>
		<?php esc_html_e( 'Explore', 'tour-booking-manager' ); ?>
		<span class="ttbm-orchid-explore-arrow" aria-hidden="true">&rarr;</span>
	</button>
</div>
