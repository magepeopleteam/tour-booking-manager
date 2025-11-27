<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$daywise = get_post_meta($ttbm_post_id, 'ttbm_daywise_details',true);
	$display_daywise = get_post_meta($ttbm_post_id, 'ttbm_display_schedule',true);

	if( !empty($daywise) && $display_daywise == 'on' ){
		?>
		<div class='day-wise-details-area'>
			<h2 class="content-title"><?php esc_html_e( "Schedule Details", 'tour-booking-manager' ); ?></h2>

			<div class='ttbm_widget_content ttbm_day_wise_details'>
				<?php
					foreach ( $daywise as $key => $day ) {
						?>
						<div class="day_wise_details_item">
							<h5 class="day_wise_details_item_title justifyBetween" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-up" data-collapse-target="#ttbm_day_datails_<?php echo esc_attr( $key ); ?>">
								<?php echo esc_html( $day['ttbm_day_title']); ?>
								<span data-icon class="fas fa-chevron-down"></span>
							</h5>
							<div data-collapse="#ttbm_day_datails_<?php echo esc_attr( $key ); ?>">
								<div class="day_wise_details_item_details ttbm_wp_editor">
									<?php echo wp_kses_post( html_entity_decode($day['ttbm_day_content'])); ?>
								</div>
							</div>
						</div>
					<?php } ?>
			</div>
		</div>
	<?php } ?>