<?php
	if ( ! defined( 'ABSPATH' ) )die;
	$ttbm_post_id          = $ttbm_post_id ?? get_the_id();
	$include_services = TTBM_Function::get_feature_list( $ttbm_post_id, 'ttbm_service_included_in_price' );
	$exclude_services = TTBM_Function::get_feature_list( $ttbm_post_id, 'ttbm_service_excluded_in_price' );
	$display_include  = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_include_service', 'on' );
	$term_name        = $term_name ?? true;
	if ( $display_include != 'off' ) {
        $list_view_task=true;
		?>
		<div class="ttbm_default_widget ">
			<?php do_action( 'ttbm_section_title', 'ttbm_string_include_price_list', esc_html__( "What's Included", 'tour-booking-manager' ) ); ?>
			
			<div class="ttbm-include-exclude">
				<div class="include items">
					<ul>
						<?php
							$counter=0;
							foreach ($include_services as $key => $services) {
								if ($key < 3):
									
									?>
									<li>
										<i class="fa fa-check"></i>
										<?php echo esc_html($services); ?>
									</li>
								<?php
								endif;
								$counter++;
							}
							$counter = $counter - 3;
						?>
					</ul>
				<p data-target-popup="include-exclude-popup"> <?php echo esc_html__( 'See ', 'tour-booking-manager' ) .  esc_html( $counter ) .  esc_html__( ' more', 'tour-booking-manager' ); ?></p></div>
				<div class="exclude items">
					<ul>
						<?php
							foreach ($exclude_services as $key => $services) {
								$term = get_term_by('name', $services, 'ttbm_tour_features_list');
								if ($term) {
									$term_name = $term_name ? $term->name : '';
									if ($key < 3):
										?>
										<li>
											<i class="fas fa-times"></i>
											<?php echo esc_html($term_name); ?>
										</li>
									<?php
									endif;
								}
							}
						?>
					</ul>
				</div>
			</div>
			
		</div>
		<div data-popup="include-exclude-popup" class="ttbm_popup ttbm_style">
			<div class="popupMainArea">
				<div class="popupHeader allCenter">
					<h2 class="ttbm_description_title _mR"><?php esc_html_e('What\'s Included','tour-booking-manager'); ?></h2>
					<span class="fas fa-times popupClose"></span>
				</div>
				<div class="popupBody">
					<div class="ttbm-include-exclude">
						<div class="include items">
							<ul>
								<?php
									foreach ($include_services as $key => $services) {
										?>
										<li>
											<i class="fa fa-check"></i>
											<?php echo esc_html($services); ?>
										</li>
										<?php
									}
								?>
							</ul>
						</div>		
						<div class="exclude items">
							<ul>
								<?php
									foreach ($exclude_services as $key => $services) {
										?>
										<li>
											<i class="fas fa-times"></i>
											<?php echo esc_html($services); ?>
										</li>
										<?php
									}
								?>
							</ul>
						</div>		
					</div>
				</div>
			</div>
		</div>
	<?php } ?>