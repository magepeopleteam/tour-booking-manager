<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id          = $ttbm_post_id ?? get_the_id();
	$exclude_services = TTBM_Function::get_feature_list( $ttbm_post_id, 'ttbm_service_excluded_in_price' );
	if ( sizeof( $exclude_services ) > 0 && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_exclude_service', 'on' ) != 'off' ) {
		?>
		<div class="ttbm_default_widget">
			<?php $this->section_title( "ttbm_string_exclude_price_list", esc_html__( "What's Excluded", "tour-booking-manager" ) ); ?>
			<div class='ttbm_widget_content'>
				<ul>
					<?php
						foreach ( $exclude_services as $services ) {
							$term = get_term_by( 'name', $services, 'ttbm_tour_features_list' );
							if ( $term ) {
								$icon = get_term_meta( $term->term_id, 'ttbm_feature_icon', true );
								$icon = $icon ?: 'fas fa-forward';
								?>
								<li>
									<span class="circleIcon_xs <?php esc_attr_e( $icon ); ?>"></span>
									<?php esc_html_e( $term->name ); ?>
								</li>
								<?php
							}
						}
					?>
				</ul>
			</div>
		</div>
		<?php } ?>