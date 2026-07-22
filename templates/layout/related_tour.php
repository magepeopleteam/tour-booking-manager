<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id    = $ttbm_post_id ?? get_the_id();
	$related_tours   = TTBM_Function::get_related_tour_ids( $ttbm_post_id );
	$related_tour_count = count( $related_tours );
	$num_of_tour     = $num_of_tour ?? '';
	if ( $related_tour_count > 0 ) {
		$num_of_tour = ( isset( $num_of_tour ) && $num_of_tour > 0 ) ? $num_of_tour : 4;
		$grid_class  = $related_tour_count <= $num_of_tour ? 'grid_' . $num_of_tour : '';
		$div_class   = 'flexWrap grid';
		?>
			<div class='ttbm_style related-hotel' id="ttbm_related_tour">
			<h2 class="content-title"><?php esc_html_e( 'Discover Your Next Adventure', 'tour-booking-manager' ); ?></h2>
			<?php
				if ( count( $related_tours ) > $num_of_tour ) {
					include TTBM_Function::template_path( 'layout/carousel_indicator.php' );
				}
			?>
			<div class="_mZero  <?php echo esc_attr( $related_tour_count > $num_of_tour ? 'owl-theme owl-carousel' : $div_class ); ?>" data-show="<?php echo esc_attr( $num_of_tour ); ?>">
				<?php foreach ( $related_tours as $related_tour_id ) { ?>
					<div class="filter_item placeholder_area <?php echo esc_attr( $grid_class ); ?>">
						<?php
						$ttbm_post_id  = $related_tour_id;
						$hide_gc_tags  = true;
						include TTBM_Function::template_path( 'list/grid_list_style.php' );
						?>
					</div>
				<?php } ?>

			</div>
		</div>
	<?php } ?>
