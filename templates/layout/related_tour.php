<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id       = $ttbm_post_id ?? get_the_id();
    $related_tours = [];
	$related_tours = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_related_tour', array());
    $related_tour_count=sizeof( $related_tours );

    if( $related_tour_count < 1 ){
        $location_name = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_location_name' );
        $related_tours = get_posts([
            'post_type' => 'ttbm_tour',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'ttbm_location_name',
                    'value' => $location_name,
                    'compare' => '='
                ]
            ]
        ]);
    }
    $related_tour_count=sizeof( $related_tours );
    if ( $related_tour_count > 1 &&  ( $key = array_search($ttbm_post_id, $related_tours ) ) !== false) {
        unset( $related_tours[$key] );
    }

    $related_tour_count=sizeof( $related_tours );
	$num_of_tour=$num_of_tour??'';
	if ( $related_tour_count > 0 ) {

        $num_of_tour=$num_of_tour>0?$num_of_tour:4;
		$num_of_tour=min($num_of_tour,$related_tour_count);
		$grid_class=$related_tour_count <= $num_of_tour?'grid_'.$num_of_tour:'';
		$div_class=$related_tour_count==1?'flexWrap modern':'flexWrap grid';
		?>
			<?php
				// Debug: output first related item's ID and thumbnail URL as an HTML comment (non-visible)
				$first_related_id = is_array($related_tours) && ! empty( $related_tours ) ? reset( $related_tours ) : '';
				if ( $first_related_id ) {
					$first_thumb = TTBM_Global_Function::get_image_url( $first_related_id );
					echo '<!-- TTBM DEBUG: first related id=' . esc_html( $first_related_id ) . ' thumb=' . esc_url( $first_thumb ) . ' -->';
				}
			?>
			<div class='ttbm_style related-hotel' id="ttbm_related_tour">
			<h2 class="content-title"><?php esc_html_e( 'You may like ', 'tour-booking-manager' ) ?></h2>
			<?php
				if ( sizeof( $related_tours ) > $num_of_tour ) {
					include( TTBM_Function::template_path( 'layout/carousel_indicator.php' ) );
				}
			?>
			<div class="_mZero  <?php echo esc_attr($related_tour_count >$num_of_tour?'owl-theme owl-carousel':$div_class); ?>" data-show="<?php echo esc_attr($num_of_tour); ?>">
				<?php foreach ( $related_tours as $ttbm_post_id ) { ?>
					<div class="filter_item placeholder_area <?php echo esc_attr($grid_class); ?>">
						<?php include( TTBM_Function::template_path( 'list/grid_list_style.php' ) ); ?>
					</div>
				<?php } ?>

			</div>
		</div>
	<?php } ?>