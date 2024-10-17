<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$tour_type = TTBM_Function::get_tour_type( $tour_id );
	$count     = $count ?? 0;
	$travel_type = TTBM_Function::get_travel_type( $tour_id );
	if ( $tour_type == 'general' && MP_Global_Function::get_post_info( $tour_id, 'ttbm_display_seat_details', 'on' ) == 'on' ) {
		$total_seat     = $total_seat??TTBM_Function::get_total_seat( $tour_id );
		$available_seat = $available_seat??TTBM_Function::get_total_available( $tour_id );
		?>

		<div class="item_icon ttbm_available_seat_area">
			<i class="fas fa-restroom"></i>
			<?php esc_html_e( 'Total Seats :', 'tour-booking-manager' ); ?>
			<strong><span class="ttbm_available_seat"><?php echo esc_html( $available_seat); ?></span>/<?php echo esc_html(  $total_seat ); ?></strong>
		</div>

		<?php
		$count ++;
	}
?>