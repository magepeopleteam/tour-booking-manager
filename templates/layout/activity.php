<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id       = $ttbm_post_id ?? get_the_id();
	$tour_activities    = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_tour_activities', array() );
	$display_activities = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_activities', 'on' );

	if ( sizeof( $tour_activities ) > 0 && $display_activities != 'off' ) {
		?>
		<section class="ttbm_planned_activities_section">
			<h2 class="ttbm_pa_heading">
				<?php TTBM_Function::translation_settings( 'ttbm_string_activities', esc_html__( 'Planned Activities', 'tour-booking-manager' ) ); ?>
			</h2>
			<ul class="ttbm_activity_pills">
				<?php
					foreach ( $tour_activities as $tour_activity ) {
						$term = get_term_by( 'id', $tour_activity, 'ttbm_tour_activities' );
						if ( ! $term ) {
							continue;
						}
						$icon = get_term_meta( $term->term_id, 'ttbm_activities_icon', true );
						$icon = $icon ?: 'fas fa-hiking';
						?>
						<li class="ttbm_activity_pill">
							<span class="ttbm_activity_pill_icon <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
							<span class="ttbm_activity_pill_label"><?php echo esc_html( $term->name ); ?></span>
						</li>
						<?php
					}
				?>
			</ul>
		</section>
		<?php
	}
