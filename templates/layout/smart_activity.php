<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id      = $ttbm_post_id ?? get_the_ID();
	$activities_terms  = TTBM_Global_Function::get_taxonomy( 'ttbm_tour_activities' );
	$tour_activities   = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_tour_activities', array() );

	if ( ! empty( $tour_activities ) && TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_activities', 'on' ) !== 'off' ) {
		?>
		<section class="ttbm_planned_activities_section">
			<h2 class="ttbm_pa_heading">
				<?php TTBM_Function::translation_settings( 'ttbm_string_activities', esc_html__( 'Planned Activities', 'tour-booking-manager' ) ); ?>
			</h2>
			<ul class="ttbm_activity_pills">
				<?php
					foreach ( $activities_terms as $activity ) {
						if ( ! in_array( $activity->term_id, $tour_activities, true ) ) {
							continue;
						}
						$icon = get_term_meta( $activity->term_id, 'ttbm_activities_icon', true );
						$icon = $icon ?: 'fas fa-hiking';
						?>
						<li class="ttbm_activity_pill">
							<span class="ttbm_activity_pill_icon <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
							<span class="ttbm_activity_pill_label"><?php echo esc_html( $activity->name ); ?></span>
						</li>
						<?php
					}
				?>
			</ul>
		</section>
		<?php
	}
