<?php	if ( ! defined( 'ABSPATH' ) ) {		die;	}	$ttbm_post_id = $ttbm_post_id ?? get_the_id();	$start_place = TTBM_Function::get_start_place( $ttbm_post_id );	$tour_type = TTBM_Function::get_tour_type( $ttbm_post_id );	$count = $count ?? 0;	if ( $start_place && $tour_type == 'general' && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_start_location', 'on' ) != 'off' ) {		?>		<div class="alignCenter small_box <?php echo esc_attr( $add_class ?? '' ); ?>" data-placeholder>			<div class="item_icon"><span class="fas fa-map-marker"></span></div>			<h6>				<?php esc_html_e( 'Start Location: ', 'tour-booking-manager' ); ?>&nbsp;				<strong><?php echo esc_html( $start_place ); ?></strong>			</h6>		</div>		<?php		$count ++;	}	?>