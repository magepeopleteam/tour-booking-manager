<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
$ttbm_post_id         = $ttbm_post_id ?? get_the_id();
	$tour_activities = MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_tour_activities', array() );
	if ( sizeof( $tour_activities ) > 0 && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_activities', 'on' ) != 'off' ) {
		?>
		<div class='ttbm_description'>
			<h2><?php esc_html_e( 'Activities ', 'tour-booking-manager'); ?></h2>
			<ul class="ttbm-activities">
				<?php foreach ( $tour_activities as $tour_activity ) {
					$term = get_term_by( 'name', $tour_activity, 'ttbm_tour_activities' );
					if ( $term ) {
						$icon = get_term_meta( $term->term_id, 'ttbm_activities_icon', true );
						$icon = $icon ?: 'fa fa-check';
						?>
						<li class="ttbm-items">
							<i class="<?php esc_attr_e( $icon ); ?>"></i>
							<?php esc_html_e( $term->name ); ?>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</div>
	<?php } ?>