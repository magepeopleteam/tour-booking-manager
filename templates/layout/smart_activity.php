<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_ID(); // fixed get_the_id() to get_the_ID()

	$tour_activities = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_tour_activities', array() );
	$tour_activities_array = explode(',', $tour_activities[0]);
	$activities_terms = TTBM_Global_Function::get_taxonomy('ttbm_tour_activities');
	
	if ( ! empty( $tour_activities ) && TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_activities', 'on' ) !== 'off' ) {
		?>
		<div class="ttbm_default_widget">
			<?php do_action( 'ttbm_section_title', 'ttbm_string_include_price_list', esc_html__( "Activities", 'tour-booking-manager' ) ); ?>
			<div class="ttbm_widget_content" style="padding: 15px;">
				<ul class="ttbm-activities">
					<?php
					foreach ( $activities_terms as $activity ) {
						if ( in_array( $activity->term_id, $tour_activities_array ) ) {
							?>
							<li class="ttbm-items">
<i class="fa fa-check"></i>
								<?php echo esc_html( $activity->name ); ?>
							</li>
							<?php
						}
					}
					?>
				</ul>
			</div>
		</div>
		<?php
	}

