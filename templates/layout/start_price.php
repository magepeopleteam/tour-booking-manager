<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$tour_id     = $tour_id ?? get_the_id();
	$start_price = $start_price ?? TTBM_Function::get_tour_start_price( $tour_id );
	if ( $start_price && TTBM_Function::get_post_info( $tour_id, 'ttbm_display_price_start', 'on' ) != 'off' ) {
		?>
		<span><?php esc_html_e( 'Price From : ', 'tour-booking-manager' ); ?>&nbsp;</span>&nbsp;
		<strong><?php echo wc_price($start_price); ?></strong>
	<?php } ?>