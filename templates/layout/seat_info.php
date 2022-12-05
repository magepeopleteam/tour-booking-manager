<?php	if ( ! defined( 'ABSPATH' ) ) {		die;	}	$tour_id   = $tour_id ?? get_the_id();	$tour_type = TTBM_Function::get_tour_type( $tour_id );	$count     = $count ?? 0;	if ( $tour_type == 'general' && TTBM_Function::get_post_info( $tour_id, 'ttbm_display_seat_details', 'on' ) == 'on' ) {		$total_seat     = TTBM_Function::get_total_seat( $tour_id );		$available_seat = TTBM_Function::get_total_available( $tour_id );		?>		<div class="alignCenter small_box <?php echo esc_attr( $add_class ?? '' ); ?>" data-placeholder>			<div class="item_icon"><span class="fas fa-chair"></span></div>			<h6>				<?php TTBM_Function::translation_settings( 'ttbm_string_total_seats', esc_html__( 'Total Seats :', 'tour-booking-manager' ) ); ?>&nbsp;				<strong><?php echo esc_html( $available_seat . '/' . $total_seat ); ?></strong>			</h6>		</div>		<?php		$count ++;	}?>