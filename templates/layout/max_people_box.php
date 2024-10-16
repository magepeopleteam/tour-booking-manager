<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id    = $ttbm_post_id ?? get_the_id();
	$max_people = MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_travel_max_people_allow');
	$tour_type  = TTBM_Function::get_tour_type( $ttbm_post_id );
	$count      = $count ?? 0;
	if ( $max_people && $tour_type == 'general' && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_max_people', 'on' ) != 'off' ) {
		?>

			<div class="item_icon">
				<i class="fas fa-users"></i>
				<?php esc_html_e( 'Max People :', 'tour-booking-manager' ); ?>&nbsp;&nbsp;
				<strong><?php echo esc_html( $max_people ); ?></strong>
			</div>

		<?php
		$count ++;
	}
?>