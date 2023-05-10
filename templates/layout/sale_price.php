<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$tour_id       = $tour_id ?? get_the_id();
	$regular_price = TTBM_Function::check_discount_price_exit( $tour_id);
	if ( $regular_price ) {
		?>
		<div class="ribbon" data-placeholder><?php esc_html_e( 'On Sale ! ', 'tour-booking-manager' ); ?></div>
		<?php
	}