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
		<div class="ttbm_description">
			<h2><?php esc_html_e( 'Activities', 'tour-booking-manager' ); ?></h2>
			<ul class="ttbm-activities">
				<?php
				foreach ( $activities_terms as $activity ) {
					if ( in_array( $activity->term_id, $tour_activities_array ) ) {
						$icon = $icon ?: 'fa fa-check';
						?>
						<li class="ttbm-items">
							<i class="<?php esc_attr_e( $icon ); ?>"></i>
							<?php esc_html_e( $activity->name ); ?>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</div>
		<?php
	}

