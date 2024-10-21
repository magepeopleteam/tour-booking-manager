<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id   = $ttbm_post_id ?? get_the_id();
	$duration  = TTBM_Function::get_duration( $ttbm_post_id );
	$night     = MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_travel_duration_night' );
	$duration_type=MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_travel_duration_type', 'day' );
	$tour_type = TTBM_Function::get_tour_type( $ttbm_post_id );
	if ( ( $duration || $night ) && $tour_type == 'general' && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_duration', 'on' ) != 'off' ) {
		?>
		<div class="ttbm_list_info" data-placeholder>
			<i class="far fa-clock mR_xs"></i>
			<?php
				if ( $duration && $duration > 1 ) {
					echo esc_html( $duration ) . ' ';
					if ($duration_type == 'day' ) {
						esc_html_e( 'Days ', 'tour-booking-manager' );
					}elseif( $duration_type == 'min' ){
						esc_html_e( 'Minutes ', 'tour-booking-manager' );
					} else {
						esc_html_e( 'Hours ', 'tour-booking-manager' );
					}
				}
				else if ( $duration && $duration <= 1 ) {
					echo esc_html( $duration ) . ' ';
					if ( $duration_type == 'day' ) {
						esc_html_e( 'Day ', 'tour-booking-manager' );
					} elseif( $duration_type== 'min' ){
						esc_html_e( 'Minute ', 'tour-booking-manager' );
					}else {
						esc_html_e( 'Hour ', 'tour-booking-manager' );
					}
				}
				if ( MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_duration_night', 'off' ) != 'off' ) {
					if ( $night && $night > 1 ) {
						echo esc_html( $night ) . ' ' . esc_html__( 'Nights ', 'tour-booking-manager' );
					}
					if ( $night && $night <= 1 ) {
						echo esc_html( $night ) . ' ' . esc_html__( 'Night ', 'tour-booking-manager' );
					}
				}
			?>
		</div>
	<?php } ?>