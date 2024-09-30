<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id     = $ttbm_post_id ?? get_the_id();
	$start_price = $start_price ?? TTBM_Function::get_tour_start_price( $ttbm_post_id );
	if ( $start_price && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_price_start', 'on' ) != 'off' ) {
		?>
		<span><?php esc_html_e( 'Price From : ', 'tour-booking-manager' ); ?></span>
		<strong><?php echo wc_price($start_price); ?></strong>
	<?php } ?>