<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$regular_price = TTBM_Function::check_discount_price_exit( $tour_id);
	if ( $regular_price ) {
		?>
		<div class="ribbon" data-placeholder><?php esc_html_e( 'On Sale ! ', 'tour-booking-manager' ); ?></div>
		<?php
	}