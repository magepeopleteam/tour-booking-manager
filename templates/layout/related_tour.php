<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id       = $ttbm_post_id ?? get_the_id();
	$related_tours = MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_related_tour', array());
	$related_tour_count=sizeof( $related_tours );
	$num_of_tour=$num_of_tour??'';
	if ( $related_tour_count > 0 && (MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_related', 'on' ) != 'off' || $num_of_tour>0)) {
		$num_of_tour=$num_of_tour>0?$num_of_tour:4;
		$num_of_tour=min($num_of_tour,$related_tour_count);
		$grid_class=$related_tour_count <= $num_of_tour?'grid_'.$num_of_tour:'';
		$div_class=$related_tour_count==1?'flexWrap modern':'flexWrap grid';
		?>
		<div class='mpStyle ttbm_wraper shadow_one' id="ttbm_related_tour">
			<div class="ttbm_container">
				<div class='ttbm_default_widget'>
					<?php do_action( 'ttbm_section_title', 'ttbm_string_related_tour', esc_html__( 'You may like Tour ', 'tour-booking-manager' ) ); ?>

					<?php
						if ( sizeof( $related_tours ) > $num_of_tour ) {
							include( TTBM_Function::template_path( 'layout/carousel_indicator.php' ) );
						}
					?>
					<div class="ttbm_widget_content _mZero  <?php echo esc_attr($related_tour_count >$num_of_tour?'owl-theme owl-carousel':$div_class); ?>" data-show="<?php echo esc_attr($num_of_tour); ?>">
						<?php foreach ( $related_tours as $ttbm_post_id ) { ?>
							<div class="filter_item <?php echo esc_attr($grid_class); ?>">
								<?php include( TTBM_Function::template_path( 'list/grid_list.php' ) ); ?>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>