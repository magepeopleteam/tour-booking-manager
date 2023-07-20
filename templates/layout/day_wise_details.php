<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id     = $ttbm_post_id ?? get_the_id();
	$day_details = MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_daywise_details', array());
	if ( sizeof( $day_details ) > 0 && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_schedule', 'on' ) != 'off' ) {
		?>
		<div class='ttbm_default_widget'>
			<?php do_action( 'ttbm_section_title', 'ttbm_string_schedule_details', esc_html__( 'Schedule Details ', 'tour-booking-manager' ) ); ?>
			<div class='ttbm_widget_content ttbm_day_wise_details'>
				<?php
					foreach ( $day_details as $key => $day ) {
						$day_title  = array_key_exists( 'ttbm_day_title', $day ) ? html_entity_decode( $day['ttbm_day_title'] ) : '';
						$day_text   = array_key_exists( 'ttbm_day_content', $day ) ? html_entity_decode( $day['ttbm_day_content'] ) : '';
						$day_images = array_key_exists( 'ttbm_day_image', $day ) ? html_entity_decode( $day['ttbm_day_image'] ) : '';
						$images     = explode( ',', $day_images );
						?>
						<div class="day_wise_details_item">
							<h5 class="day_wise_details_item_title justifyBetween" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-up" data-collapse-target="#ttbm_day_datails_<?php esc_attr_e( $key ); ?>">
								<?php echo esc_html( $day_title ); ?>
								<span data-icon class="fas fa-chevron-down"></span>
							</h5>
							<div data-collapse="#ttbm_day_datails_<?php esc_attr_e( $key ); ?>">
								<div class="day_wise_details_item_details mp_wp_editor">
									<?php
										if ( $day_images && sizeof( $images ) > 0 ) {
											do_action( 'add_mp_custom_slider_only', $images );
										}
									?>
									<?php echo do_shortcode($day_text); ?>
									<?php //echo mep_esc_html( $day_text ); ?>
								</div>
							</div>
						</div>
					<?php } ?>
			</div>
		</div>
	<?php } ?>