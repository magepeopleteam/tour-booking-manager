<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id       = $ttbm_post_id ?? get_the_id();
	$include_services   = TTBM_Function::get_feature_list( $ttbm_post_id, 'ttbm_service_included_in_price' );
	$exclude_services   = TTBM_Function::get_feature_list( $ttbm_post_id, 'ttbm_service_excluded_in_price' );
	$display_include    = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_include_service', 'on' );
	$display_exclude    = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_exclude_service', 'on' );
	$has_include        = sizeof( $include_services ) > 0 && $display_include != 'off';
	$has_exclude        = sizeof( $exclude_services ) > 0 && $display_exclude != 'off';

	if ( ! $has_include && ! $has_exclude ) {
		return;
	}
	?>
	<section class="ttbm_include_exclude_section">
		<h2 class="ttbm_pa_heading">
			<?php TTBM_Function::translation_settings( 'ttbm_string_include_exclude', esc_html__( 'Included / Exclude', 'tour-booking-manager' ) ); ?>
		</h2>
		<div class="ttbm_include_exclude_box">
			<div class="ttbm-include-exclude">
			<?php if ( $has_include ) : ?>
				<div class="ttbm_ie_col ttbm_ie_included">
					<ul>
						<?php
							foreach ( $include_services as $services ) {
								$term = get_term_by( 'name', $services, 'ttbm_tour_features_list' );
								$name = $term ? $term->name : $services;
								?>
								<li>
									<span class="ttbm_ie_icon ttbm_ie_check" aria-hidden="true"><i class="fas fa-check"></i></span>
									<?php echo esc_html( $name ); ?>
								</li>
								<?php
							}
						?>
					</ul>
				</div>
			<?php endif; ?>
			<?php if ( $has_exclude ) : ?>
				<div class="ttbm_ie_col ttbm_ie_excluded">
					<ul>
						<?php
							foreach ( $exclude_services as $services ) {
								$term = get_term_by( 'name', $services, 'ttbm_tour_features_list' );
								if ( ! $term ) {
									continue;
								}
								?>
								<li>
									<span class="ttbm_ie_icon ttbm_ie_cross" aria-hidden="true"><i class="fas fa-times"></i></span>
									<?php echo esc_html( $term->name ); ?>
								</li>
								<?php
							}
						?>
					</ul>
				</div>
			<?php endif; ?>
			</div>
		</div>
	</section>
