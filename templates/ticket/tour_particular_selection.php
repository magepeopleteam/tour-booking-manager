<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$all_dates     = $all_dates ?? TTBM_Function::get_date( $tour_id );
	$travel_type   = $travel_type ?? TTBM_Function::get_travel_type( $tour_id );
	$tour_type     = $tour_type ?? TTBM_Function::get_tour_type( $tour_id );
	$template_name = $template_name ?? MP_Global_Function::get_post_info( $tour_id, 'ttbm_theme_file', 'default.php' );
	if ( sizeof( $all_dates ) > 0 && $tour_type == 'general' && $travel_type == 'particular' ) {
		?>
		<div class="_shadow_two_dFlex ttbm_particular_date_area">
			<?php if ( $template_name == 'default.php' ) { ?>
				<h3 class="mR">
					<?php include( TTBM_Function::template_path( 'layout/start_price.php' ) ); ?><br/>
					<small><?php esc_html_e( 'Free Cancellation before 24 hours.', 'tour-booking-manager' ); ?></small>
				</h3>
			<?php } ?>
			<button class="_themeButton_fullWidth ttbm_scroll_to_particular_date" type="button">
				<?php esc_html_e( 'Check  Availability', 'tour-booking-manager' ); ?>
			</button>
		</div>
	<?php } ?>