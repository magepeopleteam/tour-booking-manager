<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$age_range = MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_travel_min_age');
	$tour_type = TTBM_Function::get_tour_type( $ttbm_post_id );
	$count = $count ?? 0;
	if ( $age_range && $tour_type == 'general' && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_min_age', 'on' ) != 'off' ) {
		?>
		
			<div class="item_icon">
				<i class="far fa-grin"></i>
				<?php esc_html_e( 'Age Range :', 'tour-booking-manager' ); ?>
				<strong><?php echo esc_html( $age_range ); ?></strong>
			</div>

		<?php
		$count ++;
	}
	?>