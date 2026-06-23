<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$concierge_url = apply_filters( 'ttbm_orchid_concierge_url', '#' );
?>
<div class="filter_item ttbm-orchid-concierge grid_3">
	<div class="ttbm-orchid-concierge-inner">
		<span class="ttbm-orchid-concierge-icon" aria-hidden="true">+</span>
		<h4 class="ttbm-orchid-concierge-title"><?php esc_html_e( "Can't find what you need?", 'tour-booking-manager' ); ?></h4>
		<p class="ttbm-orchid-concierge-text"><?php esc_html_e( 'Create a custom itinerary with our luxury concierge service.', 'tour-booking-manager' ); ?></p>
		<a class="ttbm-orchid-concierge-link" href="<?php echo esc_url( $concierge_url ); ?>">
			<?php esc_html_e( 'Contact Concierge', 'tour-booking-manager' ); ?>
		</a>
	</div>
</div>
