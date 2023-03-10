<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$tour_id = $tour_id ?? get_the_id();
	$upcoming_date    = TTBM_Function::get_post_info( $tour_id, 'ttbm_upcoming_date' );
	$tour_type = TTBM_Function::get_tour_type( $tour_id );
	if ( ! $upcoming_date && $tour_type == 'general') { ?>
		<div class="ttbm_list_info _bT_bgWarning" data-placeholder>
			<?php esc_html_e( 'Expired !', 'tour-booking-manager' ); ?>
		</div>
		<?php
	}
	$travel_type = TTBM_Function::get_travel_type( $tour_id );
	if ( $upcoming_date && $tour_type == 'general'  && $travel_type == 'fixed') {
		$available_seat = TTBM_Function::get_total_available( $tour_id );
		if ( $available_seat < 1 ) {
			?>
			<div class="ttbm_list_info _bT_bgWarning" data-placeholder>
				<?php esc_html_e( 'Fully Booked !', 'tour-booking-manager' ); ?>
			</div>
			<?php
		}
	}
	?>